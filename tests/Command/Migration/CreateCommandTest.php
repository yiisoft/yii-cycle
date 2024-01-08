<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Migration;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Migration\CreateCommand;
use Yiisoft\Yii\Cycle\Tests\Command\Stub\FakeOutput;

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
                $this->callback(static fn (string $name): bool => \str_contains($name, 'OrmTestDatabase')),
                $this->callback(static fn (string $name): bool =>
                    \str_contains($name, 'OrmTestDatabase') &&
                    \str_contains($name, 'namespace Test\\Migration') &&
                    \str_contains($name, 'use Cycle\\Migrations\\Migration') &&
                    \str_contains($name, 'protected const DATABASE = \'testDatabase\'') &&
                    \str_contains($name, 'public function up(): void') &&
                    \str_contains($name, 'public function down(): void')
                )
            );

        $command = new CreateCommand(new CycleDependencyProxy(new SimpleContainer([
            DatabaseProviderInterface::class => $databaseProvider,
            Migrator::class => new Migrator(
                $config,
                $this->createMock(DatabaseProviderInterface::class),
                $repository
            ),
            MigrationConfig::class => $config,
        ])));

        $output = new FakeOutput();
        $code = $command->run(new ArrayInput(['name' => 'foo']), $output);

        $this->assertSame(ExitCode::OK, $code);
        $this->assertStringContainsString('New migration file has been created', $output->getBuffer());
    }
}
