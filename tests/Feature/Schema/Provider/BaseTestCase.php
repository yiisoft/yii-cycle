<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\SQLite\MemoryConnectionConfig;
use Cycle\Database\Config\SQLiteDriverConfig;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;

abstract class BaseTestCase extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $params['yiisoft/yii-cycle']['dbal'] = new DatabaseConfig([
            'default' => 'default',
            'databases' => ['default' => ['connection' => 'sqlite']],
            'connections' => [
                'sqlite' => new SQLiteDriverConfig(connection: new MemoryConnectionConfig()),
            ],
        ]);
        $diConfig = require \dirname(__DIR__, 4) . '/config/di.php';
        $diConfig[Aliases::class] = new Aliases(['@test' => __DIR__ . '/Stub']);

        $this->container = new Container(ContainerConfig::create()->withDefinitions($diConfig));
    }
}
