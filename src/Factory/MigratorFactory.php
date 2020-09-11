<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Psr\Container\ContainerInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\FileRepository;
use Spiral\Migrations\Migrator;

final class MigratorFactory
{
    public function __invoke(ContainerInterface $container)
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
