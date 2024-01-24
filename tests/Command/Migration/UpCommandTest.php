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
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Migration\UpCommand;
use Yiisoft\Yii\Cycle\Tests\Command\Stub\FakeMigration;

final class UpCommandTest extends TestCase
{
    public function testExecuteWithoutMigrations(): void
    {
        $config = new MigrationConfig();

        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->once())->method('getMigrations')->willReturn([]);

        $migrator = new Migrator(
            $config,
            $this->createMock(DatabaseProviderInterface::class),
            $repository
        );

        $output = new BufferedOutput();
        $command = new UpCommand(
            new CycleDependencyProxy(new SimpleContainer([
                Migrator::class => $migrator,
                MigrationConfig::class => $config,
            ])),
            $this->createMock(EventDispatcherInterface::class)
        );
        $code = $command->run(new ArrayInput([]), $output);

        $this->assertSame(ExitCode::OK, $code);
        $this->assertStringContainsString('No migration found for execute', $output->fetch());
    }

    public function testExecute(): void
    {
        $config = new MigrationConfig(['safe' => true]);

        $migration = new FakeMigration();
        $migration = $migration->withState(new State('test', new \DateTimeImmutable(), State::STATUS_PENDING));

        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(3))->method('getMigrations')->willReturn([$migration]);

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

        $output = new BufferedOutput();
        $command = new UpCommand(
            new CycleDependencyProxy(new SimpleContainer([
                Migrator::class => $migrator,
                MigrationConfig::class => $config,
            ])),
            $this->createMock(EventDispatcherInterface::class)
        );

        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $code = $command->run($input, $output);

        $result = $output->fetch();

        $this->assertSame(ExitCode::OK, $code);
        $this->assertStringContainsString('Migration to be applied:', $result);
        $this->assertStringContainsString('test: executed', $result);
    }
}
