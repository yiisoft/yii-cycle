<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Schema\SchemaPhpCommand;

use Cycle\ORM\SchemaInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Schema\SchemaPhpCommand;

use function dirname;

final class SchemaPhpCommandTest extends TestCase
{
    private BufferedOutput $output;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();
    }

    public function testExecuteWithoutFile(): void
    {
        $schema = $this->createMock(SchemaInterface::class);
        $schema->expects($this->any())->method('getRoles')->willReturn(['foo', 'bar']);
        $schema->expects($this->any())->method('define')->willReturnCallback(
            fn (string $role, int $property): ?string => $property === SchemaInterface::ROLE ? $role : null
        );

        $container = new SimpleContainer([SchemaInterface::class => $schema]);
        $promise = new CycleDependencyProxy($container);
        $command = new SchemaPhpCommand(new Aliases(), $promise);

        $code = $command->run(new ArrayInput([]), $this->output);
        $result = $this->output->fetch();

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('Schema::ROLE => \'foo\'', $result);
        $this->assertStringContainsString('Schema::ROLE => \'bar\'', $result);
    }

    public function testExecuteWithFile(): void
    {
        $file = dirname(__DIR__, 2) . '/Stub/schema.php';

        $schema = $this->createMock(SchemaInterface::class);
        $schema->expects($this->any())->method('getRoles')->willReturn(['foo', 'bar']);
        $schema->expects($this->any())->method('define')->willReturnCallback(
            fn (string $role, int $property): ?string => $property === SchemaInterface::ROLE ? $role : null
        );

        $container = new SimpleContainer([SchemaInterface::class => $schema]);
        $promise = new CycleDependencyProxy($container);
        $command = new SchemaPhpCommand(new Aliases(), $promise);

        $code = $command->run(new ArrayInput(['file' => $file]), $this->output);
        $result = $this->output->fetch();

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString(sprintf('Destination: %s', $file), $result);

        $this->assertStringContainsString('Schema::ROLE => \'foo\'', file_get_contents($file));
        $this->assertStringContainsString('Schema::ROLE => \'bar\'', file_get_contents($file));

        \unlink($file);
    }

    public function testExecuteWithFileAndAlias(): void
    {
        $file = dirname(__DIR__, 2) . '/Stub/alias-schema.php';

        $schema = $this->createMock(SchemaInterface::class);
        $schema->expects($this->any())->method('getRoles')->willReturn(['foo', 'bar']);
        $schema->expects($this->any())->method('define')->willReturnCallback(
            fn (string $role, int $property): ?string => $property === SchemaInterface::ROLE ? $role : null
        );

        $container = new SimpleContainer([SchemaInterface::class => $schema]);
        $promise = new CycleDependencyProxy($container);
        $command = new SchemaPhpCommand(new Aliases([
            '@test' => dirname(__DIR__, 2) . '/Stub',
        ]), $promise);

        $code = $command->run(new ArrayInput(['file' => '@test/alias-schema.php']), $this->output);
        $result = $this->output->fetch();

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString(sprintf('Destination: %s', $file), $result);

        $this->assertStringContainsString('Schema::ROLE => \'foo\'', file_get_contents($file));
        $this->assertStringContainsString('Schema::ROLE => \'bar\'', file_get_contents($file));

        \unlink($file);
    }

    public function testExecuteWithMissingDirectory(): void
    {
        $file = dirname(__DIR__, 2) . '/Stub/Foo/schema.php';

        $schema = $this->createMock(SchemaInterface::class);
        $schema->expects($this->any())->method('getRoles')->willReturn(['foo', 'bar']);
        $schema->expects($this->any())->method('define')->willReturnCallback(
            fn (string $role, int $property): ?string => $property === SchemaInterface::ROLE ? $role : null
        );

        $container = new SimpleContainer([SchemaInterface::class => $schema]);
        $promise = new CycleDependencyProxy($container);
        $command = new SchemaPhpCommand(new Aliases(), $promise);

        $code = $command->run(new ArrayInput(['file' => $file]), $this->output);
        $this->assertSame(Command::FAILURE, $code);

        $output = $this->output->fetch();
        $this->assertStringContainsString('Destination:', $output);

        if (DIRECTORY_SEPARATOR === '/') {
            $this->assertStringContainsString('/tests/Command/Stub/Foo/schema.php', $output);
        }

        $this->assertStringContainsString('Destination directory', $output);
        $this->assertStringContainsString('not found', $output);
    }
}
