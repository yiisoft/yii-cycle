<?php

namespace Yiisoft\Yii\Cycle\Command;

use Spiral\Migrations\MigrationInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;

class DownCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/down';

    public function configure(): void
    {
        $this
            ->setDescription('Rollback last migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // todo: drop cached schema
        // $this->cycleOrmHelper->dropCurrentSchemaCache();

        $this->findMigrations($output);

        try {
            $migration = $this->migrator->rollback();
            if (!$migration instanceof MigrationInterface) {
                throw new \Exception('Migration not found');
            }

            $state = $migration->getState();
            $status = $state->getStatus();
            $output->writeln(
                sprintf('<fg=cyan>%s</>: %s', $state->getName(), static::$migrationStatus[$status] ?? $status)
            );
        } catch (\Throwable $e) {
            $output->writeln([
                '<fg=red>Error!</>',
                $e->getMessage(),
            ]);
            return $e->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }
}
