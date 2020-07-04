<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Command\CycleDependencyPromise;
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
    CycleDependencyPromise::class => static function (ContainerInterface $container) {
        return new CycleDependencyPromise($container);
    }
];
