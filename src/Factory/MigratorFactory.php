<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\Database\DatabaseManager;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\FileRepository;
use Cycle\Migrations\Migrator;
use Psr\Container\ContainerInterface;
use Spiral\Core\FactoryInterface;

final class MigratorFactory
{
    public function __invoke(ContainerInterface $container): Migrator
    {
        $migConf = $container->get(MigrationConfig::class);
        $dbal = $container->get(DatabaseManager::class);

        $migrator = new Migrator(
            $migConf,
            $dbal,
            new FileRepository($migConf, $container->get(FactoryInterface::class))
        );
        // Init migration table
        $migrator->configure();
        return $migrator;
    }
}
