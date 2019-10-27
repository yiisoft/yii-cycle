<?php

namespace Yiisoft\Yii\Cycle\Factory;

use Psr\Container\ContainerInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\DbalConfig;

class DbalFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(DbalConfig::class);
        $conf = $config->prepareConfig();
        return new DatabaseManager($conf);
    }
}
