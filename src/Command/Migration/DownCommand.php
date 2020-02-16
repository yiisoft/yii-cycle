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

final class DownCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/down';

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(CycleDependencyPromise $promise, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($promise);
    }

    public function configure(): void
    {
        $this
            ->setDescription('Rollback last migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrations = $this->findMigrations($output);
        // check any executed migration
        $exist = false;
        foreach ($migrations as $migration) {
            if ($migration->getState()->getStatus() === State::STATUS_EXECUTED) {
                $exist = true;
                break;
            }
        }
        if (!$exist) {
            $output->writeln('<fg=red>No migration found for rollback</>');
            return ExitCode::OK;
        }

        $this->eventDispatcher->dispatch(new BeforeMigrate());
        try {
            $this->promise->getMigrator()->rollback();
            if (!$migration instanceof MigrationInterface) {
                throw new \Exception('Migration not found');
            }

            $state = $migration->getState();
            $status = $state->getStatus();
            $output->writeln(
                sprintf('<fg=cyan>%s</>: %s', $state->getName(), static::MIGRATION_STATUS[$status] ?? $status)
            );
        } finally {
            $this->eventDispatcher->dispatch(new AfterMigrate());
        }
        return ExitCode::OK;
    }
}
