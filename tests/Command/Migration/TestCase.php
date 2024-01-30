<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Migration;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\SQLite\MemoryConnectionConfig;
use Cycle\Database\Config\SQLiteDriverConfig;
use Cycle\Database\DatabaseManager;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Cycle\Migrations\State;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Yiisoft\Yii\Cycle\Tests\Command\Stub\FakeMigration;

abstract class TestCase extends BaseTestCase
{
    protected static function migrator(MigrationConfig $config, RepositoryInterface $repository): Migrator
    {
        return new Migrator($config, static::databaseManager(), $repository);
    }

    protected static function databaseManager(): DatabaseManager
    {
        return new DatabaseManager(new DatabaseConfig([
            'default' => 'default',
            'databases' => ['default' => ['connection' => 'sqlite']],
            'connections' => [
                'sqlite' => new SQLiteDriverConfig(connection: new MemoryConnectionConfig()),
            ],
        ]));
    }

    protected static function migration(): FakeMigration
    {
        $migration = new FakeMigration();
        $migration = $migration->withState(new State('test', new \DateTimeImmutable(), State::STATUS_PENDING));

        return $migration;
    }
}
