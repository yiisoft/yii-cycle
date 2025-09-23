<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Migration;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\CommandTester;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Migration\GenerateCommand;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;

final class GenerateCommandTest extends TestCase
{
    public function testExecuteWithOutstandingMigrations(): void
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(1))->method('getMigrations')->willReturn([self::migration()]);

        $migrator = self::migrator(new MigrationConfig(), $repository);
        $migrator->configure();

        $output = new BufferedOutput();
        $command = $this->createCommand(
            $migrator,
            $this->createMock(DatabaseProviderInterface::class),
            new MigrationConfig()
        );
        $code = $command->run(new ArrayInput([]), $output);

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('Outstanding migrations found, run `migrate:up` first.', $output->fetch());
    }

    public function testExecuteWithoutChanges(): void
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(2))->method('getMigrations')->willReturn([]);

        $migrator = self::migrator(new MigrationConfig(), $repository);
        $migrator->configure();

        $output = new BufferedOutput();
        $command = $this->createCommand(
            $migrator,
            $this->createMock(DatabaseProviderInterface::class),
            new MigrationConfig()
        );

        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $code = $command->run($input, $output);

        $result = $output->fetch();

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('Added 0 file(s)', $result);
        $this->assertStringContainsString(
            'If you want to create new empty migration, use migrate:create',
            $result
        );
    }

    public function testExecute(): void
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(2))->method('getMigrations')->willReturnOnConsecutiveCalls(
            [],
            [self::migration()]
        );

        $migrator = self::migrator(new MigrationConfig(), $repository);
        $migrator->configure();

        $output = new BufferedOutput();
        $command = $this->createCommand(
            $migrator,
            $this->createMock(DatabaseProviderInterface::class),
            new MigrationConfig()
        );

        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $code = $command->run($input, $output);

        $result = $output->fetch();

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('Added 1 file(s)', $result);
        $this->assertStringContainsString('test', $result);
    }

    public function testExecuteWithoutChangesWithCreatingNewMigration(): void
    {
        $config = new MigrationConfig(['namespace' => 'Test\\Migration']);

        $database = $this->createMock(DatabaseInterface::class);
        $database->expects($this->once())->method('getName')->willReturn('testDatabase');

        $databaseProvider = $this->createMock(DatabaseProviderInterface::class);
        $databaseProvider->expects($this->once())->method('database')->willReturn($database);

        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(2))->method('getMigrations')->willReturn([]);
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

        $migrator = self::migrator($config, $repository);
        $migrator->configure();

        $output = new BufferedOutput();
        $command = $this->createCommand($migrator, $databaseProvider, $config);

        $input = new ArrayInput([]);
        $input->setInteractive(true);

        $series = [
            [[$input, $output, new ConfirmationQuestion(
                'Would you like to create empty migration right now? (Y/n)',
                true
            )], true],
            [[$input, $output, new Question('Please enter an unique name for the new migration: ')], 'foo'],
        ];
        $helper = $this->createMock(QuestionHelper::class);
        $helper
            ->expects($this->exactly(2))
            ->method('ask')
            ->willReturnCallback(function (mixed ...$args) use (&$series) {
                [$expectedArgs, $return] = \array_shift($series);
                $this->assertEquals($expectedArgs, $args);

                return $return;
            });

        $command->setHelperSet(new HelperSet(['question' => $helper]));

        $code = $command->run($input, $output);

        $result = $output->fetch();

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('Added 0 file(s)', $result);
        $this->assertStringContainsString(
            'If you want to create new empty migration, use migrate:create',
            $result
        );
    }

    public function testExecuteWithoutChangesWithCreatingNewMigrationEmptyName(): void
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(2))->method('getMigrations')->willReturn([]);
        $repository->expects($this->never())->method('registerMigration');

        $migrator = self::migrator(new MigrationConfig(), $repository);
        $migrator->configure();

        $output = new BufferedOutput();
        $command = $this->createCommand(
            $migrator,
            $this->createMock(DatabaseProviderInterface::class),
            new MigrationConfig()
        );

        $input = new ArrayInput([]);
        $input->setInteractive(true);

        $series = [
            [[$input, $output, new ConfirmationQuestion(
                'Would you like to create empty migration right now? (Y/n)',
                true
            )], true],
            [[$input, $output, new Question('Please enter an unique name for the new migration: ')], ''],
        ];
        $helper = $this->createMock(QuestionHelper::class);
        $helper
            ->expects($this->exactly(2))
            ->method('ask')
            ->willReturnCallback(function (mixed ...$args) use (&$series) {
                [$expectedArgs, $return] = \array_shift($series);
                $this->assertEquals($expectedArgs, $args);

                return $return;
            });

        $command->setHelperSet(new HelperSet(['question' => $helper]));

        $code = $command->run($input, $output);

        $result = $output->fetch();

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('Added 0 file(s)', $result);
        $this->assertStringContainsString('You entered an empty name. Exit', $result);
    }

    public function testExecuteWithoutConfirmation(): void
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(2))->method('getMigrations')->willReturn([]);

        $migrator = self::migrator(new MigrationConfig(), $repository);
        $migrator->configure();

        $command = $this->createCommand(
            $migrator,
            $this->createMock(DatabaseProviderInterface::class),
            new MigrationConfig(),
        );

        $commandTester = new CommandTester($command);
        $command->setHelperSet(new HelperSet(['question' => new QuestionHelper()]));
        $commandTester->setInputs(['no']);

        $code = $commandTester->execute([], options: ['interactive' => true]);
        $this->assertSame(Command::SUCCESS, $code);
        $this->assertSame(
            implode(PHP_EOL, [
                'Added 0 file(s)',
                'If you want to create new empty migration, use migrate:create',
                'Would you like to create empty migration right now? (Y/n)',
            ]),
            $commandTester->getDisplay(),
        );
    }

    private function createCommand(
        Migrator $migrator,
        DatabaseProviderInterface $dbProvider,
        MigrationConfig $config
    ): GenerateCommand {
        return new GenerateCommand(new CycleDependencyProxy(new SimpleContainer([
            Migrator::class => $migrator,
            MigrationConfig::class => $config,
            SchemaConveyorInterface::class => $this->createMock(SchemaConveyorInterface::class),
            DatabaseProviderInterface::class => $dbProvider,
        ])));
    }
}
