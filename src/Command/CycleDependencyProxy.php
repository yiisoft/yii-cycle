<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migrator;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Provider\SchemaProviderInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;

final class CycleDependencyProxy
{
    public function __construct(private ContainerInterface $container)
    {
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

    public function getSchemaProvider(): SchemaProviderInterface
    {
        return $this->container->get(SchemaProviderInterface::class);
    }

    public function getSchemaConveyor(): SchemaConveyorInterface
    {
        return $this->container->get(SchemaConveyorInterface::class);
    }
}
