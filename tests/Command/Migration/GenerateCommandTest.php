<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Migration;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\SQLite\MemoryConnectionConfig;
use Cycle\Database\Config\SQLiteDriverConfig;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Cycle\Migrations\State;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Migration\GenerateCommand;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;
use Yiisoft\Yii\Cycle\Tests\Command\Stub\FakeMigration;
use Yiisoft\Yii\Cycle\Tests\Command\Stub\FakeOutput;

final class GenerateCommandTest extends TestCase
{
    public function testExecuteWithOutstandingMigrations(): void
    {
        $migration = new FakeMigration();
        $migration = $migration->withState(new State('test', new \DateTimeImmutable(), State::STATUS_PENDING));

        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(1))->method('getMigrations')->willReturn([$migration]);

        $migrator = new Migrator(
            new MigrationConfig(),
            new DatabaseManager(new DatabaseConfig([
                'default' => 'default',
                'databases' => ['default' => ['connection' => 'sqlite']],
                'connections' => [
                    'sqlite' => new SQLiteDriverConfig(connection: new MemoryConnectionConfig()),
                ],
            ])),
            $repository
        );
        $migrator->configure();

        $output = new FakeOutput();
        $command = new GenerateCommand(new CycleDependencyProxy(new SimpleContainer([
            Migrator::class => $migrator,
            MigrationConfig::class => new MigrationConfig(),
        ])));
        $code = $command->run(new ArrayInput([]), $output);

        $this->assertSame(ExitCode::OK, $code);
        $this->assertStringContainsString('Outstanding migrations found, run `migrate/up` first.', $output->getBuffer());
    }

    public function testExecuteWithoutChanges(): void
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(2))->method('getMigrations')->willReturn([]);

        $migrator = new Migrator(
            new MigrationConfig(),
            new DatabaseManager(new DatabaseConfig([
                'default' => 'default',
                'databases' => ['default' => ['connection' => 'sqlite']],
                'connections' => [
                    'sqlite' => new SQLiteDriverConfig(connection: new MemoryConnectionConfig()),
                ],
            ])),
            $repository
        );
        $migrator->configure();

        $output = new FakeOutput();
        $command = new GenerateCommand(new CycleDependencyProxy(new SimpleContainer([
            Migrator::class => $migrator,
            MigrationConfig::class => new MigrationConfig(),
            SchemaConveyorInterface::class => $this->createMock(SchemaConveyorInterface::class),
            DatabaseProviderInterface::class => $this->createMock(DatabaseProviderInterface::class),
        ])));

        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $code = $command->run($input, $output);

        $this->assertSame(ExitCode::OK, $code);
        $this->assertStringContainsString('Added 0 file(s)', $output->getBuffer());
        $this->assertStringContainsString(
            'If you want to create new empty migration, use migrate/create',
            $output->getBuffer()
        );
    }

    public function testExecute(): void
    {
        $migration = new FakeMigration();
        $migration = $migration->withState(new State('test', new \DateTimeImmutable(), State::STATUS_PENDING));

        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(2))->method('getMigrations')->willReturnOnConsecutiveCalls(
            [],
            [$migration]
        );

        $migrator = new Migrator(
            new MigrationConfig(),
            new DatabaseManager(new DatabaseConfig([
                'default' => 'default',
                'databases' => ['default' => ['connection' => 'sqlite']],
                'connections' => [
                    'sqlite' => new SQLiteDriverConfig(connection: new MemoryConnectionConfig()),
                ],
            ])),
            $repository
        );
        $migrator->configure();

        $output = new FakeOutput();
        $command = new GenerateCommand(new CycleDependencyProxy(new SimpleContainer([
            Migrator::class => $migrator,
            MigrationConfig::class => new MigrationConfig(),
            SchemaConveyorInterface::class => $this->createMock(SchemaConveyorInterface::class),
            DatabaseProviderInterface::class => $this->createMock(DatabaseProviderInterface::class),
        ])));

        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $code = $command->run($input, $output);

        $this->assertSame(ExitCode::OK, $code);
        $this->assertStringContainsString('Added 1 file(s)', $output->getBuffer());
        $this->assertStringContainsString('test', $output->getBuffer());
    }
}
