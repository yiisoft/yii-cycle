<?php

use Spiral\Database\Driver\SQLite\SQLiteDriver;
use Yiisoft\Yii\Cycle\Command;

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
    'cycle.dbal' => [
        'default' => 'default',
        'aliases' => [],
        'databases' => [
            'default' => ['connection' => 'sqlite']
        ],
        'connections' => [
            'sqlite' => [
                'driver' => SQLiteDriver::class,
                'connection' => 'sqlite:@runtime/database.db',
                'username' => '',
                'password' => '',
            ]
        ],
    ],

    // common paths config
    'cycle.locator' => [
        'entityPaths' => [
            '@src/Entity'
        ],
    ],

    // migration config
    'cycle.migrations' => [
        'directory' => '@root/migrations',
        'namespace' => 'App\\Migration',
        'table' => 'migration',
        'safe' => false,
    ],

];
