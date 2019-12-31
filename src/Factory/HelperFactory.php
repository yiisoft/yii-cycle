<?php

namespace Yiisoft\Yii\Cycle\Factory;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;
use Yiisoft\Yii\Cycle\SchemaConveyorInterface;

final class HelperFactory
{
    private array $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function __invoke(ContainerInterface $container)
    {
        $helper = new CycleOrmHelper(
            $container->get(DatabaseManager::class),
            $container->get(CacheInterface::class),
            $container->get(SchemaConveyorInterface::class)
        );

        if (isset($this->params['cacheKey'])) {
            $helper->setCacheKey($this->params['cacheKey']);
        }

        return $helper;
    }
}
