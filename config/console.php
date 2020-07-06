<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Factory\MigrationConfigFactory;
use Yiisoft\Yii\Cycle\Factory\MigratorFactory;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Migrator;

/**
 * @var array $params
 */

return [
    Migrator::class => new MigratorFactory(),
    MigrationConfig::class => new MigrationConfigFactory($params['yiisoft/yii-cycle']['migrations']),
    CycleDependencyProxy::class => static function (ContainerInterface $container) {
        return new CycleDependencyProxy($container);
    }
];
