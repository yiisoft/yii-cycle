# Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```
composer require yiisoft/yii-cycle
```

## Configuring extension

File `config/params.php`:
```php
<?php
return [
    # Cycle DBAL config
    'cycle.dbal' => [
        'default' => 'default',
        'aliases' => [],
        'databases' => [
            'default' => ['connection' => 'sqlite']
        ],
        'connections' => [
            'sqlite' => [
                'driver' => \Spiral\Database\Driver\SQLite\SQLiteDriver::class,
                'connection' => 'sqlite:@runtime/database.db',
                'username' => '',
                'password' => '',
            ]
        ],
    ],

    # Cycle common config
    'cycle.common' => [
        'entityPaths' => [
            '@src/Entity'
        ],
        # Cache
        'cacheEnabled' => true,
        'cacheKey' => 'Cycle-ORM-Schema',

        # Дополнительные генераторы, запускаемые при расчёте схемы
        # Массив определений \Cycle\Schema\GeneratorInterface
        'generators' => [
            # sync table changes to database
            // \Cycle\Schema\Generator\SyncTables::class,
        ],

        # \Cycle\ORM\PromiseFactoryInterface definition
        # ProxyFactory require a cycle/proxy-factory extension
        // 'promiseFactory' => \Cycle\ORM\Promise\ProxyFactory::class,

        # \Psr\Log\LoggerInterface definition
        'queryLogger' => null,
    ],

    // Cycle migration config
    'cycle.migrations' => [
        'directory' => '@root/migrations',
        'namespace' => 'App\\Migration',
        'table' => 'migration',
        'safe' => false,
    ],
];
```
Read more in the Cycle documentation:

- [Connect to Database](https://github.com/cycle/docs/blob/master/basic/connect.md)
- [References and Proxies](https://github.com/cycle/docs/blob/master/advanced/promise.md)
