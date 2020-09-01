<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;
use Spiral\Database\DatabaseProviderInterface;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Migrator;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaManager;

final class CycleDependencyProxy
{
    private ContainerInterface $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    public function getDatabaseProvider(): DatabaseProviderInterface
    {
        return $this->container->get(DatabaseProviderInterface::class);
    }
    public function getMigrationConfig(): MigrationConfig
    {
        return $this->container->get(MigrationConfig::class);
    }
    public function getMigrator(): Migrator
    {
        return $this->container->get(Migrator::class);
    }
    /**
     * Can be used in other packages
     */
    public function getORM(): ORMInterface
    {
        return $this->container->get(ORMInterface::class);
    }
    public function getSchema(): SchemaInterface
    {
        return $this->container->get(SchemaInterface::class);
    }
    public function getSchemaManager(): SchemaManager
    {
        return $this->container->get(SchemaManager::class);
    }
    public function getSchemaConveyor(): SchemaConveyorInterface
    {
        return $this->container->get(SchemaConveyorInterface::class);
    }
}
