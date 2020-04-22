# Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/):

```
composer require yiisoft/yii-cycle
```

## Configuring extension

If you use Yii with `composer-config-plugin`, Yii-Cycle settings could be specified in `config/params.php`:

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
        # Entity directories list
        'entityPaths' => [
            '@src/Entity'
        ],
        # Turn on cache usage for getting DB schema
        'cacheEnabled' => true,
        # Key to use for cache
        'cacheKey' => 'Cycle-ORM-Schema',

        # Additional generators, launched when computing schema
        # Array of \Cycle\Schema\GeneratorInterface definitions
        'generators' => [
            # The following generator allows to apply schema changes to DB without migrations
            // \Cycle\Schema\Generator\SyncTables::class,
        ],

        # \Cycle\ORM\PromiseFactoryInterface definition
        'promiseFactory' => null, # use Promise objects
        # ProxyFactory requires cycle/proxy-factory package
        // 'promiseFactory' => \Cycle\ORM\Promise\ProxyFactory::class,

        # SQL query logger
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

Read more in Cycle documentation:

- [Connect to Database](https://github.com/cycle/docs/blob/master/basic/connect.md)
- [References and Proxies](https://github.com/cycle/docs/blob/master/advanced/promise.md)
