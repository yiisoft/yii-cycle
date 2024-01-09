<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Factory;

use Cycle\Database\Config\ConnectionConfig;
use PHPUnit\Framework\TestCase;
use Yiisoft\Injector\Injector;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Factory\CycleDynamicFactory;
use Yiisoft\Yii\Cycle\Tests\Unit\Stub\FakeDriverConfig;

final class CycleDynamicFactoryTest extends TestCase
{
    public function testMake(): void
    {
        $factory = new CycleDynamicFactory(new Injector(new SimpleContainer([
            ConnectionConfig::class => $this->createMock(ConnectionConfig::class)
        ])));

        $this->assertInstanceOf(
            FakeDriverConfig::class,
            $factory->make(FakeDriverConfig::class, ['driver' => 'foo'])
        );
    }
}
