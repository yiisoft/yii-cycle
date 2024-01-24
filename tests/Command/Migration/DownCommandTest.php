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
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Migration\DownCommand;
use Yiisoft\Yii\Cycle\Command\Migration\UpCommand;
use Yiisoft\Yii\Cycle\Tests\Command\Stub\FakeMigration;

final class DownCommandTest extends TestCase
{
    public function testExecuteWithoutMigrations(): void
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->once())->method('getMigrations')->willReturn([]);

        $migrator = new Migrator(
            new MigrationConfig(),
            $this->createMock(DatabaseProviderInterface::class),
            $repository
        );

        $output = new BufferedOutput();
        $command = new DownCommand(
            new CycleDependencyProxy(new SimpleContainer([
                Migrator::class => $migrator,
                MigrationConfig::class => new MigrationConfig(),
            ])),
            $this->createMock(EventDispatcherInterface::class)
        );
        $code = $command->run(new ArrayInput([]), $output);

        $this->assertSame(ExitCode::OK, $code);
        $this->assertStringContainsString('No migration found for rollback', $output->fetch());
    }

    public function testExecute(): void
    {
        $migration = new FakeMigration();
        $migration = $migration->withState(new State('test', new \DateTimeImmutable(), State::STATUS_PENDING));

        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(5))->method('getMigrations')->willReturn([$migration]);

        $db = new DatabaseManager(new DatabaseConfig([
            'default' => 'default',
            'databases' => ['default' => ['connection' => 'sqlite']],
            'connections' => [
                'sqlite' => new SQLiteDriverConfig(connection: new MemoryConnectionConfig()),
            ],
        ]));

        $migrator = new Migrator(new MigrationConfig(), $db, $repository);
        $migrator->configure();

        $promise = new CycleDependencyProxy(new SimpleContainer([
            DatabaseProviderInterface::class => $db,
            Migrator::class => $migrator,
            MigrationConfig::class => new MigrationConfig(),
        ]));

        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $command = new UpCommand($promise, $this->createMock(EventDispatcherInterface::class));
        $command->run($input, new NullOutput());

        $command = new DownCommand($promise, $this->createMock(EventDispatcherInterface::class));
        $output = new BufferedOutput();
        $code = $command->run($input, $output);

        $result = $output->fetch();

        $this->assertSame(ExitCode::OK, $code);
        $this->assertStringContainsString('Total 1 migration(s) found', $result);
        $this->assertStringContainsString('test: pending', $result);
    }
}
