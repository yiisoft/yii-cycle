<?php

use App\Console\Command\CreateUser;
use Yiisoft\Yii\Cycle\Factory\MigrationConfigFactory;
use Yiisoft\Yii\Cycle\Factory\MigratorFactory;
use Psr\Container\ContainerInterface;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Migrator;
use Yiisoft\Aliases\Aliases;

/**
 * @var array $params
 */

return [
    Migrator::class => new MigratorFactory(),
    MigrationConfig::class => new MigrationConfigFactory($params['cycle.migrations']),

];
