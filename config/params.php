<?php

use Yiisoft\Yii\Cycle\Command;

return [
    // Console commands
    'commands' => [
        'migrate/create' => Command\CreateCommand::class,
        'migrate/generate' => Command\GenerateCommand::class,
        'migrate/up' => Command\UpCommand::class,
        'migrate/down' => Command\DownCommand::class,
        'migrate/list' => Command\ListCommand::class,
    ],

    // DBAL config
    'cycle.dbal' => [
        'default' => null,
        'aliases' => [],
        'databases' => [],
        'connections' => [],
    ],

    // common config
    'cycle.common' => [
        'entityPaths' => [],
        'cacheKey' => 'Cycle-ORM-Schema',
        'generators' => [],
    ],

    // migration config
    'cycle.migrations' => [
        'directory' => '@root/migrations',
        'namespace' => 'App\\Migration',
        'table' => 'migration',
        'safe' => false,
    ],

];
