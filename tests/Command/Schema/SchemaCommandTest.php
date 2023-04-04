<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Schema;

use Cycle\ORM\SchemaInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Schema\SchemaCommand;

final class SchemaCommandTest extends TestCase
{
    private OutputInterface $output;

    protected function setUp(): void
    {
        $this->output = new class extends Output {
            private string $buffer = '';

            protected function doWrite(string $message, bool $newline): void
            {
                $this->buffer .= $message;

                if ($newline) {
                    $this->buffer .= \PHP_EOL;
                }
            }

            public function getBuffer(): string
            {
                return $this->buffer;
            }
        };
    }

    public function testExecuteUndefinedRoles(): void
    {
        $schema = $this->getMockBuilder(SchemaInterface::class)->getMock();
        $schema->expects($this->any())->method('getRoles')->willReturn([]);

        $container = new SimpleContainer([SchemaInterface::class => $schema]);
        $promise = new CycleDependencyProxy($container);
        $command = new SchemaCommand($promise);

        $code = $command->run(new ArrayInput(['role' => 'foo,bar']), $this->output);

        $this->assertEquals(ExitCode::OK, $code);
        $this->assertStringContainsString('Undefined roles: foo, bar', $this->output->getBuffer());
    }

    public function testExecuteGetRoles(): void
    {
        $schema = $this->getMockBuilder(SchemaInterface::class)->getMock();
        $schema->expects($this->any())->method('getRoles')->willReturn(['foo', 'bar']);
        $schema->expects($this->any())->method('define')->willReturnCallback(
            function (string $role, int $property) {
                if ($property === SchemaInterface::ROLE) {
                    return $role;
                }

                return null;
            }
        );

        $container = new SimpleContainer([SchemaInterface::class => $schema]);
        $promise = new CycleDependencyProxy($container);
        $command = new SchemaCommand($promise);

        $code = $command->run(new ArrayInput([]), $this->output);

        $this->assertEquals(ExitCode::OK, $code);
        $this->assertStringNotContainsString('Undefined roles', $this->output->getBuffer());
    }
}
