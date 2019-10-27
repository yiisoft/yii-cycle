<?php

use Yiisoft\Yii\Cycle\Factory\MigrationConfigFactory;
use Yiisoft\Yii\Cycle\Factory\MigratorFactory;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Migrator;

/**
 * @var array $params
 */

return [
    Migrator::class => new MigratorFactory(),
    MigrationConfig::class => new MigrationConfigFactory(),

];
