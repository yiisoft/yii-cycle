<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Migration;

use Cycle\Migrations\MigrationInterface;
use Cycle\Migrations\State;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Event\AfterMigrate;
use Yiisoft\Yii\Cycle\Event\BeforeMigrate;

final class DownCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/down';
    protected static $defaultDescription = 'Rolls back the last applied migration';

    private ?EventDispatcherInterface $eventDispatcher;

    public function __construct(CycleDependencyProxy $promise, ?EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($promise);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrations = $this->findMigrations($output);
        // check any executed migration
        foreach (array_reverse($migrations) as $migration) {
            if ($migration->getState()->getStatus() === State::STATUS_EXECUTED) {
                $exist = true;
                break;
            }
        }
        if (!isset($migration)) {
            $output->writeln('<fg=red>No migration found for rollback</>');
            return ExitCode::OK;
        }

        $migrator = $this->promise->getMigrator();

        // Confirm
        if (!$migrator->getConfig()->isSafe()) {
            $output->writeln('<fg=yellow>Migration to be reverted:</>');
            $output->writeln('— <fg=cyan>' . $migration->getState()->getName() . '</>');
            if ($input->isInteractive()) {
                $question = new ConfirmationQuestion('Revert the above migration? (yes|no) ', false);
                if (!$this->getHelper('question')->ask($input, $output, $question)) {
                    return ExitCode::OK;
                }
            }
        }

        $this->eventDispatcher?->dispatch(new BeforeMigrate());
        try {
            $migrator->rollback();
            if (!$migration instanceof MigrationInterface) {
                throw new \Exception('Migration not found');
            }

            $state = $migration->getState();
            $status = $state->getStatus();
            $output->writeln(
                sprintf('<fg=cyan>%s</>: %s', $state->getName(), self::MIGRATION_STATUS[$status] ?? $status)
            );
        } finally {
            $this->eventDispatcher?->dispatch(new AfterMigrate());
        }
        return ExitCode::OK;
    }
}
