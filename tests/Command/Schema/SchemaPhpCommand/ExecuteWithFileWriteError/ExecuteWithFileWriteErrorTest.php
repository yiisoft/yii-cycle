<?php

declare(strict_types=1);

namespace Command\Schema\SchemaPhpCommand\ExecuteWithFileWriteError;

use Cycle\ORM\SchemaInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Schema\SchemaPhpCommand;

final class ExecuteWithFileWriteErrorTest extends TestCase
{
    protected function setUp(): void
    {
        set_error_handler(static fn() => true, E_WARNING);
    }

    protected function tearDown(): void
    {
        restore_error_handler();
    }

    /**
     * @requires OS Linux
     */
    public function testBase(): void
    {
        $file = __DIR__ . '/schema-0444.php';
        $bufferedOutput = new BufferedOutput();

        $schema = $this->createMock(SchemaInterface::class);
        $schema->expects($this->any())->method('getRoles')->willReturn(['foo', 'bar']);
        $schema->expects($this->any())->method('define')->willReturnCallback(
            fn(string $role, int $property): ?string => $property === SchemaInterface::ROLE ? $role : null
        );

        $container = new SimpleContainer([SchemaInterface::class => $schema]);
        $promise = new CycleDependencyProxy($container);
        $command = new SchemaPhpCommand(new Aliases(), $promise);

        $code = $command->run(new ArrayInput(['file' => $file]), $bufferedOutput);
        $this->assertSame(Command::FAILURE, $code);

        $output = $bufferedOutput->fetch();
        $this->assertStringContainsString('Destination:', $output);

        if (DIRECTORY_SEPARATOR === '/') {
            $this->assertStringContainsString($file, $output);
        }

        $this->assertStringContainsString('Failed to write content to file.', $output);
    }
}
