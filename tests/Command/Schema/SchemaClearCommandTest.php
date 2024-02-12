<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Schema;

use Cycle\Schema\Provider\SchemaProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Schema\SchemaClearCommand;

final class SchemaClearCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $schemaProvider = $this->getMockBuilder(SchemaProviderInterface::class)->getMock();
        $schemaProvider->expects($this->once())->method('clear');
        $container = new SimpleContainer([SchemaProviderInterface::class => $schemaProvider]);
        $promise = new CycleDependencyProxy($container);
        $command = new SchemaClearCommand($promise);
        $input = $this->getMockBuilder(InputInterface::class)->getMock();
        $output = $this->getMockBuilder(OutputInterface::class)->getMock();

        $code = $command->run($input, $output);

        $this->assertEquals(ExitCode::OK, $code);
    }
}
