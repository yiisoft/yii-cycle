<?php

namespace Yiisoft\Yii\Cycle\Command;

use Spiral\Migrations\MigrationInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;

class UpCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/up';

    public function configure(): void
    {
        $this
            ->setDescription('Execute all new migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // todo: drop cached schema
        // $this->cycleOrmHelper->dropCurrentSchemaCache();

        $this->findMigrations($output);

        $limit = PHP_INT_MAX;
        try {
            do {
                $migration = $this->migrator->run();
                if (!$migration instanceof MigrationInterface) {
                    break;
                }

                $state = $migration->getState();
                $status = $state->getStatus();
                $output->writeln('<fg=cyan>' . $state->getName() . '</>: '
                    . (static::$migrationStatus[$status] ?? $status));
            } while (--$limit > 0);
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
