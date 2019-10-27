<?php

use Yiisoft\Yii\Cycle\Command;
use Yiisoft\Yii\Cycle;

return [
    // Console commands
    'commands' => [
        // 'migrate/create' => Command\CreateCommand::class,
        'migrate/generate' => Command\GenerateCommand::class,
        'migrate/up' => Command\UpCommand::class,
        'migrate/down' => Command\DownCommand::class,
        'migrate/list' => Command\ListCommand::class,
    ],

    // DBAL config
    Cycle\DbalConfig::class => [
        'default' => null,
        'aliases' => [],
        'databases' => [],
        'connections' => [],
    ],

    // common config
    Cycle\CommonConfig::class => [
        'entityPaths' => [],
        'cacheKey' => 'Cycle-ORM-Schema',
    ],

    // migration config
    Cycle\MigrationConfig::class => [
        'directory' => '@root/migrations',
        'namespace' => 'App\\Migration',
        'table' => 'migration',
        'safe' => false,
    ],

];
