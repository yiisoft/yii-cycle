<?php

namespace Yiisoft\Yii\Cycle\Factory;

use Psr\Container\ContainerInterface;
use Spiral\Migrations\Config\MigrationConfig;
use Yiisoft\Yii\Cycle;

class MigrationConfigFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $conf = $container->get(Cycle\MigrationConfig::class);
        return new MigrationConfig($conf->toArray());
    }
}
