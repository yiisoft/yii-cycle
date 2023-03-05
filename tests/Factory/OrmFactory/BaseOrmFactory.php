<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Factory\OrmFactory;

use Cycle\Database\DatabaseManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;
use Spiral\Core\FactoryInterface;
use Yiisoft\Injector\Injector;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Factory\CycleDynamicFactory;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;
use Yiisoft\Yii\Cycle\Tests\Stub\FakeConnectionConfig;
use Yiisoft\Yii\Cycle\Tests\Stub\FakeDriver;
use Yiisoft\Yii\Cycle\Tests\Stub\FakeDriverConfig;

/**
 * @psalm-import-type CollectionsConfig from OrmFactory
 */
abstract class BaseOrmFactory extends TestCase
{
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer(
            [
                NullLogger::class => new NullLogger(),
            ],
            function (string $id) {
                if ($id === Injector::class) {
                    return new Injector($this->container);
                }
                if ($id === FactoryInterface::class) {
                    return new CycleDynamicFactory($this->container->get(Injector::class));
                }
                if ($id === DatabaseManager::class) {
                    return (new DbalFactory([
                        'query-logger' => null,
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
                    ]))(
                        $this->container,
                    );
                }
                throw new NotFoundException($id);
            }
        );
    }

    /**
     * @param array $collectionsConfig
     */
    protected function makeFactory(array $collectionsConfig = []): \Cycle\ORM\FactoryInterface
    {
        return (new OrmFactory($collectionsConfig))(
            $this->container->get(\Cycle\Database\DatabaseManager::class),
            $this->container->get(\Spiral\Core\FactoryInterface::class),
            $this->container->get(\Yiisoft\Injector\Injector::class),
        );
    }
}
