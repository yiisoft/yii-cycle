<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Migration;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Migration\DownCommand;
use Yiisoft\Yii\Cycle\Command\Migration\UpCommand;

final class DownCommandTest extends TestCase
{
    public function testExecuteWithoutMigrations(): void
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->once())->method('getMigrations')->willReturn([]);

        $output = new BufferedOutput();
        $command = new DownCommand(
            new CycleDependencyProxy(new SimpleContainer([
                Migrator::class => $this->migrator(new MigrationConfig(), $repository),
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
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(5))->method('getMigrations')->willReturn([$this->migration()]);

        $migrator = $this->migrator(new MigrationConfig(), $repository);
        $migrator->configure();

        $promise = new CycleDependencyProxy(new SimpleContainer([
            DatabaseProviderInterface::class => $this->databaseManager(),
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

    public function testMigrationNotFoundException(): void
    {
        $migration = $this->migration();

        $repository = $this->createMock(RepositoryInterface::class);
        $repository
            ->expects($this->exactly(5))
            ->method('getMigrations')
            ->willReturn([$migration], [$migration], [$migration], [$migration], []);

        $migrator = $this->migrator(new MigrationConfig(), $repository);
        $migrator->configure();

        $promise = new CycleDependencyProxy(new SimpleContainer([
            DatabaseProviderInterface::class => $this->databaseManager(),
            Migrator::class => $migrator,
            MigrationConfig::class => new MigrationConfig(),
        ]));

        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $command = new UpCommand($promise, $this->createMock(EventDispatcherInterface::class));
        $command->run($input, new NullOutput());

        $command = new DownCommand($promise, $this->createMock(EventDispatcherInterface::class));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Migration not found');
        $command->run($input, new NullOutput());
    }

    public function testAbortRollback(): void
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(4))->method('getMigrations')->willReturn([$this->migration()]);

        $migrator = $this->migrator(new MigrationConfig(), $repository);
        $migrator->configure();

        $promise = new CycleDependencyProxy(new SimpleContainer([
            DatabaseProviderInterface::class => $this->databaseManager(),
            Migrator::class => $migrator,
            MigrationConfig::class => new MigrationConfig(),
        ]));

        $input = new ArrayInput([]);
        $input->setInteractive(false);
        (new UpCommand($promise, $this->createMock(EventDispatcherInterface::class)))
            ->run($input, new NullOutput());

        $command = new DownCommand($promise, $this->createMock(EventDispatcherInterface::class));
        $output = new BufferedOutput();
        $input->setInteractive(true);
        $helper = $this->createMock(QuestionHelper::class);
        $helper
            ->expects($this->once())
            ->method('ask')
            ->with(
                $input,
                $output,
                $this->equalTo(new ConfirmationQuestion('Revert the above migration? (yes|no) ', false))
            )
            ->willReturn(false);

        $command->setHelperSet(new HelperSet(['question' => $helper]));
        $code = $command->run($input, $output);

        $result = $output->fetch();

        $this->assertSame(ExitCode::OK, $code);
        $this->assertStringContainsString('Total 1 migration(s) found', $result);
        $this->assertStringNotContainsString('test: pending', $result);
    }
}
