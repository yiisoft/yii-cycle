<?php

use Yiisoft\Yii\Cycle\Command\Migration;
use Yiisoft\Yii\Cycle\Command\Common;

return [
    // Console commands
    'console' => [
        'commands' => [
            'cycle/schema' => Common\SchemaCommand::class,
            'cycle/schema/save' => Common\SchemaSaveCommand::class,
            'migrate/create' => Migration\CreateCommand::class,
            'migrate/generate' => Migration\GenerateCommand::class,
            'migrate/up' => Migration\UpCommand::class,
            'migrate/down' => Migration\DownCommand::class,
            'migrate/list' => Migration\ListCommand::class,
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
