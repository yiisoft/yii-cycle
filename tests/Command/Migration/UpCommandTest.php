<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Migration;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Migration\UpCommand;
use Yiisoft\Yii\Cycle\Event\AfterMigrate;
use Yiisoft\Yii\Cycle\Event\BeforeMigrate;

final class UpCommandTest extends TestCase
{
    public function testExecuteWithoutMigrations(): void
    {
        $config = new MigrationConfig();

        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->once())->method('getMigrations')->willReturn([]);

        $migrator = self::migrator($config, $repository);

        $output = new BufferedOutput();
        $command = new UpCommand(
            new CycleDependencyProxy(new SimpleContainer([
                Migrator::class => $migrator,
                MigrationConfig::class => $config,
            ])),
            $this->createMock(EventDispatcherInterface::class)
        );
        $code = $command->run(new ArrayInput([]), $output);

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('No migration found for execute', $output->fetch());
    }

    public function testExecute(): void
    {
        $config = new MigrationConfig(['safe' => true]);

        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(3))->method('getMigrations')->willReturn([self::migration()]);

        $migrator = self::migrator(new MigrationConfig(), $repository);
        $migrator->configure();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                $this->logicalOr(
                    $this->equalTo(new BeforeMigrate()),
                    $this->equalTo(new AfterMigrate()),
                ),
            );
        $command = new UpCommand(
            new CycleDependencyProxy(new SimpleContainer([
                Migrator::class => $migrator,
                MigrationConfig::class => $config,
            ])),
            $eventDispatcher,
        );

        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $output = new BufferedOutput(decorated: true);
        $code = $command->run($input, $output);
        $this->assertSame(Command::SUCCESS, $code);

        $newLine = PHP_EOL;
        $expectedOutput = "\033[32mTotal 1 migration(s) found in \033[39m$newLine" .
            "\033[33mMigration to be applied:\033[39m$newLine" .
            "— \033[36mtest\033[39m$newLine" .
            "\033[36mtest\033[39m: executed$newLine";
        $this->assertSame($expectedOutput, $output->fetch());
    }

    /**
     * @dataProvider abortMigrationsDataProvider
     */
    public function testAbortMigrate(array $migrations, string $question): void
    {
        $config = new MigrationConfig(['safe' => true]);

        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->once())->method('getMigrations')->willReturn($migrations);

        $migrator = self::migrator(new MigrationConfig(), $repository);
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
        $input->setInteractive(true);

        $helper = $this->createMock(QuestionHelper::class);
        $helper
            ->expects($this->once())
            ->method('ask')
            ->with(
                $input,
                $output,
                $this->equalTo(new ConfirmationQuestion($question, false))
            )
            ->willReturn(false);

        $command->setHelperSet(new HelperSet(['question' => $helper]));

        $code = $command->run($input, $output);

        $result = $output->fetch();

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString(
            \count($migrations) === 1 ? 'Migration to be applied:' : '2 migrations to be applied:',
            $result
        );
        $this->assertStringNotContainsString('test: executed', $result);
    }

    public static function abortMigrationsDataProvider(): \Traversable
    {
        yield [[self::migration()], 'Apply the above migration? (yes|no) '];
        yield [[self::migration(), self::migration()], 'Apply the above migrations? (yes|no) '];
    }
}
