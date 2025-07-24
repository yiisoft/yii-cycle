<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Factory\DbalFactory;

use Cycle\Database\Config\DatabaseConfig;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Tests\Unit\Stub\FakeConnectionConfig;
use Yiisoft\Yii\Cycle\Tests\Unit\Stub\FakeDriver;
use Yiisoft\Yii\Cycle\Tests\Unit\Stub\FakeDriverConfig;

final class DbalFactoryTest extends BaseDbalFactory
{
    public function testPrepareConfig(): void
    {
        $config = [
            'query-logging' => true,
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'fake'],
            ],
            'connections' => [
                'fake' => new FakeDriverConfig(
                    connection: new FakeConnectionConfig(),
                    driver: FakeDriver::class,
                ),
            ],
        ];

        $factory = new DbalFactory();
        $ref = new \ReflectionMethod($factory, 'prepareConfig');
        $ref->setAccessible(true);

        $this->assertEquals(new DatabaseConfig($config), $ref->invoke($factory, $config));

        $obj = new DatabaseConfig($config);
        $this->assertSame($obj, $ref->invoke($factory, $obj));
    }
}
