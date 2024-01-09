<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Factory;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\SQLite\MemoryConnectionConfig;
use Cycle\Database\Config\SQLiteDriverConfig;
use Cycle\Database\DatabaseManager;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\FileRepository;
use PHPUnit\Framework\TestCase;
use Spiral\Core\FactoryInterface;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Factory\MigratorFactory;

final class MigratorFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $defaultConfig = new MigrationConfig();

        $db = new DatabaseManager(new DatabaseConfig([
            'default' => 'default',
            'databases' => ['default' => ['connection' => 'sqlite']],
            'connections' => [
                'sqlite' => new SQLiteDriverConfig(connection: new MemoryConnectionConfig()),
            ],
        ]));

        $this->assertFalse($db->database('default')->hasTable($defaultConfig->getTable()));

        $factory = new MigratorFactory();
        $migrator = $factory(new SimpleContainer([
            MigrationConfig::class => $defaultConfig,
            DatabaseManager::class => $db,
            FactoryInterface::class => $this->createMock(FactoryInterface::class)
        ]));

        $configRef = new \ReflectionProperty($migrator, 'config');
        $configRef->setAccessible(true);

        $dbalRef = new \ReflectionProperty($migrator, 'dbal');
        $dbalRef->setAccessible(true);

        $repositoryRef = new \ReflectionProperty($migrator, 'repository');
        $repositoryRef->setAccessible(true);

        $this->assertTrue($db->database('default')->hasTable($defaultConfig->getTable()));
        $this->assertSame($defaultConfig, $configRef->getValue($migrator));
        $this->assertSame($db, $dbalRef->getValue($migrator));
        $this->assertInstanceOf(FileRepository::class, $repositoryRef->getValue($migrator));
    }
}
