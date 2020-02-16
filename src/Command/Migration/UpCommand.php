<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Migration;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Migrations\MigrationInterface;
use Spiral\Migrations\State;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyPromise;
use Yiisoft\Yii\Cycle\Event\AfterMigrate;
use Yiisoft\Yii\Cycle\Event\BeforeMigrate;

final class UpCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/up';

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(CycleDependencyPromise $promise, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($promise);
    }

    public function configure(): void
    {
        $this
            ->setDescription('Execute all new migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrations = $this->findMigrations($output);
        // check any not executed migration
        $exist = false;
        foreach ($migrations as $migration) {
            if ($migration->getState()->getStatus() === State::STATUS_PENDING) {
                $exist = true;
                break;
            }
        }
        if (!$exist) {
            $output->writeln('<fg=red>No migration found for execute</>');
            return ExitCode::OK;
        }
        $migrator = $this->promise->getMigrator();

        $limit = PHP_INT_MAX;
        $this->eventDispatcher->dispatch(new BeforeMigrate());
        try {
            do {
                $migration = $migrator->run();
                if (!$migration instanceof MigrationInterface) {
                    break;
                }

                $state = $migration->getState();
                $status = $state->getStatus();
                $output->writeln('<fg=cyan>' . $state->getName() . '</>: '
                    . (static::MIGRATION_STATUS[$status] ?? $status));
            } while (--$limit > 0);
        } finally {
            $this->eventDispatcher->dispatch(new AfterMigrate());
        }
        return ExitCode::OK;
    }
}
