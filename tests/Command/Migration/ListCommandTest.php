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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Migration\ListCommand;
use Yiisoft\Yii\Cycle\Tests\Command\Stub\FakeMigration;

final class ListCommandTest extends TestCase
{
    public function testExecute(): void
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

        $output = new BufferedOutput();
        $command = new ListCommand(
            new CycleDependencyProxy(new SimpleContainer([
                Migrator::class => $migrator,
                MigrationConfig::class => new MigrationConfig(),
            ])),
        );
        $code = $command->run(new ArrayInput([]), $output);

        $result = $output->fetch();

        $this->assertSame(ExitCode::OK, $code);
        $this->assertStringContainsString('Total 1 migration(s) found', $result);
        $this->assertStringContainsString('test [pending]', $result);
    }
}
