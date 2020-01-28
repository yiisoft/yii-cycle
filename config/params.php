<?php

use Yiisoft\Yii\Cycle\Command;

return [
    // Console commands
    'console' => [
        'commands' => [
            'cycle/schema' => Command\SchemaCommand::class,
            'migrate/create' => Command\CreateCommand::class,
            'migrate/generate' => Command\GenerateCommand::class,
            'migrate/up' => Command\UpCommand::class,
            'migrate/down' => Command\DownCommand::class,
            'migrate/list' => Command\ListCommand::class,
        ],
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
        'cacheEnabled' => true,
        'cacheKey' => 'Cycle-ORM-Schema',
        // List of \Cycle\Schema\GeneratorInterface definitions
        'generators' => [],
        // \Cycle\ORM\PromiseFactoryInterface definition
        'promiseFactory' => null,
        // \Psr\Log\LoggerInterface definition
        'queryLogger' => null,
    ],

    // migration config
    'cycle.migrations' => [
        'directory' => '@root/migrations',
        'namespace' => 'App\\Migration',
        'table' => 'migration',
        'safe' => false,
    ],

];
