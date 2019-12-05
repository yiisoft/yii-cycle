<?php

namespace Yiisoft\Yii\Cycle\Command;

use Cycle\Migrations\MigrationImage;
use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Exception\RepositoryException;
use Spiral\Migrations\MigrationInterface;
use Spiral\Migrations\Migrator;
use Spiral\Migrations\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;

abstract class BaseMigrationCommand extends Command
{

    protected DatabaseManager $dbal;
    protected MigrationConfig $config;
    protected Migrator $migrator;
    protected CycleOrmHelper $cycleOrmHelper;

    protected static $migrationStatus = [
        State::STATUS_UNDEFINED => 'undefined',
        State::STATUS_PENDING => 'pending',
        State::STATUS_EXECUTED => 'executed',
    ];

    public function __construct(
        DatabaseManager $dbal,
        MigrationConfig $conf,
        Migrator $migrator,
        CycleOrmHelper $cycleOrmHelper
    ) {
        parent::__construct();
        $this->dbal = $dbal;
        $this->config = $conf;
        $this->migrator = $migrator;
        $this->cycleOrmHelper = $cycleOrmHelper;
    }

    protected function createEmptyMigration(OutputInterface $output, string $name, ?string $database = null): ?MigrationImage
    {
        if ($database === null) {
            // get default database
            $database = $this->dbal->database()->getName();
        }

        $migrationSkeleton = new MigrationImage($this->config, $database);
        $migrationSkeleton->setName($name);
        try {
            $migrationFile = $this->migrator->getRepository()->registerMigration(
                $migrationSkeleton->buildFileName(),
                $migrationSkeleton->getClass()->getName(),
                $migrationSkeleton->getFile()->render()
            );
        } catch (RepositoryException $e) {
            $output->writeln('<fg=yellow>Can not create migration</>');
            $output->writeln('<fg=red>' . $e->getMessage() . '</>');
            return null;
        }
        $output->writeln('<info>New migration file has been created</info>');
        $output->writeln("<fg=cyan>{$migrationFile}</>");
        return $migrationSkeleton;
    }

    /**
     * @param OutputInterface $output
     * @return MigrationInterface[]
     */
    protected function findMigrations(OutputInterface $output): array
    {
        $list = $this->migrator->getMigrations();
        $output->writeln('<info>' . count($list) . ' migration(s) found in ' . $this->config->getDirectory() . '</info>');
        return $list;
    }
}
