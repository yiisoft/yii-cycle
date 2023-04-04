<?php

namespace Yiisoft\Yii\Cycle\Tests\Command\Schema;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Schema\SchemaRebuildCommand;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class SchemaRebuildCommandTest extends TestCase
{
    public function testExecute()
    {
        $provider = $this->getMockBuilder(SchemaProviderInterface::class)->getMock();
        $provider->expects($this->once())->method('clear');
        $provider->expects($this->once())->method('read');

        $container = new SimpleContainer([SchemaProviderInterface::class => $provider]);
        $promise = new CycleDependencyProxy($container);

        $command = new SchemaRebuildCommand($promise);

        $code = $command->run(new ArrayInput([]), new BufferedOutput);

        $this->assertEquals(ExitCode::OK, $code);
    }
}
