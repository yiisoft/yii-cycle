<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Migration;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Exception\RepositoryException;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Migration\CreateCommand;

final class CreateCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $config = new MigrationConfig(['namespace' => 'Test\\Migration']);

        $database = $this->createMock(DatabaseInterface::class);
        $database->expects($this->once())->method('getName')->willReturn('testDatabase');

        $databaseProvider = $this->createMock(DatabaseProviderInterface::class);
        $databaseProvider->expects($this->once())->method('database')->willReturn($database);

        $repository = $this->createMock(RepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('registerMigration')
            ->with(
                'testDatabase_foo',
                $this->callback(static fn (string $class): bool => \str_contains($class, 'OrmTestDatabase')),
                $this->callback(
                    static fn (string $body): bool =>
                    \str_contains($body, 'OrmTestDatabase') &&
                    \str_contains($body, 'namespace Test\\Migration') &&
                    \str_contains($body, 'use Cycle\\Migrations\\Migration') &&
                    \str_contains($body, 'protected const DATABASE = \'testDatabase\'') &&
                    \str_contains($body, 'public function up(): void') &&
                    \str_contains($body, 'public function down(): void')
                )
            );

        $command = new CreateCommand(new CycleDependencyProxy(new SimpleContainer([
            DatabaseProviderInterface::class => $databaseProvider,
            Migrator::class => self::migrator($config, $repository),
            MigrationConfig::class => $config,
        ])));

        $output = new BufferedOutput(decorated: true);
        $code = $command->run(new ArrayInput(['name' => 'foo']), $output);
        $this->assertSame(Command::SUCCESS, $code);

        $newLine = PHP_EOL;
        $expectedOutput = "\033[32mNew migration file has been created\033[39m$newLine$newLine";
        $this->assertSame($expectedOutput, $output->fetch());
    }

    public function testCreateEmptyMigrationException(): void
    {
        $config = new MigrationConfig(['namespace' => 'Test\\Migration']);

        $repository = $this->createMock(RepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('registerMigration')
            ->willThrowException(new RepositoryException('test'));

        $command = new CreateCommand(new CycleDependencyProxy(new SimpleContainer([
            DatabaseProviderInterface::class => $this->createMock(DatabaseProviderInterface::class),
            Migrator::class => self::migrator($config, $repository),
            MigrationConfig::class => $config,
        ])));

        $output = new BufferedOutput(decorated: true);
        $code = $command->run(new ArrayInput(['name' => 'foo']), $output);
        $this->assertSame(Command::SUCCESS, $code);

        $newLine = PHP_EOL;
        $expectedOutput = "\033[33mCan not create migration\033[39m$newLine" .
            "\033[31mtest\033[39m$newLine";
        $this->assertSame($expectedOutput, $output->fetch());
    }
}
