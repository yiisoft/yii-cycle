<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Provider\SchemaProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;

final class CycleDependencyProxyTest extends TestCase
{
    public function testGetDatabaseProvider(): void
    {
        $databaseProvider = $this->createMock(DatabaseProviderInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(DatabaseProviderInterface::class)
            ->willReturn($databaseProvider);

        $proxy = new CycleDependencyProxy($container);

        $this->assertSame($databaseProvider, $proxy->getDatabaseProvider());
    }

    public function testGetMigrationConfig(): void
    {
        $config = new MigrationConfig();
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(MigrationConfig::class)
            ->willReturn($config);

        $proxy = new CycleDependencyProxy($container);

        $this->assertSame($config, $proxy->getMigrationConfig());
    }

    public function testGetMigrator(): void
    {
        $migrator = new Migrator(
            new MigrationConfig(),
            $this->createMock(DatabaseProviderInterface::class),
            $this->createMock(RepositoryInterface::class)
        );
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(Migrator::class)
            ->willReturn($migrator);

        $proxy = new CycleDependencyProxy($container);

        $this->assertSame($migrator, $proxy->getMigrator());
    }

    public function testGetOrm(): void
    {
        $orm = $this->createMock(ORMInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(ORMInterface::class)
            ->willReturn($orm);

        $proxy = new CycleDependencyProxy($container);

        $this->assertSame($orm, $proxy->getORM());
    }

    public function testGetSchema(): void
    {
        $schema = $this->createMock(SchemaInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(SchemaInterface::class)
            ->willReturn($schema);

        $proxy = new CycleDependencyProxy($container);

        $this->assertSame($schema, $proxy->getSchema());
    }

    public function testGetSchemaProvider(): void
    {
        $schemaProvider = $this->createMock(SchemaProviderInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(SchemaProviderInterface::class)
            ->willReturn($schemaProvider);

        $proxy = new CycleDependencyProxy($container);

        $this->assertSame($schemaProvider, $proxy->getSchemaProvider());
    }

    public function testGetSchemaConveyor(): void
    {
        $schemaConveyor = $this->createMock(SchemaConveyorInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(SchemaConveyorInterface::class)
            ->willReturn($schemaConveyor);

        $proxy = new CycleDependencyProxy($container);

        $this->assertSame($schemaConveyor, $proxy->getSchemaConveyor());
    }
}
