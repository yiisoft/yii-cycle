<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Migration;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Migration\ListCommand;

final class ListCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects($this->exactly(1))->method('getMigrations')->willReturn([self::migration()]);

        $migrator = self::migrator(new MigrationConfig(), $repository);
        $migrator->configure();

        $output = new BufferedOutput(decorated: true);
        $command = new ListCommand(
            new CycleDependencyProxy(new SimpleContainer([
                Migrator::class => $migrator,
                MigrationConfig::class => new MigrationConfig(),
            ])),
        );
        $code = $command->run(new ArrayInput([]), $output);
        $this->assertSame(Command::SUCCESS, $code);

        $newLine = PHP_EOL;
        $expectedOutput = "\033[32mTotal 1 migration(s) found in \033[39m$newLine" .
            "\033[36mtest\033[39m \033[33m[pending]\033[39m$newLine";
        $this->assertSame($expectedOutput, $output->fetch());
    }
}
