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
        'cacheEnabled' => true,
        'cacheKey' => 'Cycle-ORM-Schema',
        // List of definitions of \Cycle\Schema\GeneratorInterface implementations
        'generators' => [],
        // Classname or instance of \Cycle\ORM\PromiseFactoryInterface
        'promiseFactory' => null,
        // Classname or instance of \Psr\Log\LoggerInterface::class
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
