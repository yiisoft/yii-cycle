# Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/):

```
composer require yiisoft/yii-cycle
```

## Configuring extension

If you use Yii with `composer-config-plugin`, Yii-Cycle settings could be specified in `config/params.php`:

```php
<?php
use Cycle\Schema\Generator;

return [
    // Common Cycle config
    'yiisoft/yii-cycle' => [
        // Cycle DBAL config
        'dbal' => [
            /**
             * SQL query logger
             * You may use {@see \Yiisoft\Yii\Cycle\Logger\StdoutQueryLogger} class to pass log to
             * stdout or any PSR-compatible logger
             */
            'query-logger' => null,
            // Default database (from 'databases' list)
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'sqlite']
            ],
            'connections' => [
                // Example SQLite connection:
                'sqlite' => [
                    'driver' => \Spiral\Database\Driver\SQLite\SQLiteDriver::class,
                    'connection' => 'sqlite:@runtime/database.db',
                    'username' => '',
                    'password' => '',
                ]
            ],
        ],

        // Migrations config
        'migrations' => [
            'directory' => '@root/migrations',
            'namespace' => 'App\\Migration',
            'table' => 'migration',
            'safe' => false,
        ],

        /**
         * {@see \Yiisoft\Yii\Cycle\Factory\OrmFactory} config 
         * Either {@see \Cycle\ORM\PromiseFactoryInterface} implementation or null is specified.
         * Docs: @link https://github.com/cycle/docs/blob/master/advanced/promise.md
         */
        'orm-promise-factory' => null,

        /**
         * A list of DB schema providers for {@see \Yiisoft\Yii\Cycle\Schema\SchemaManager}
         * Providers are implementing {@see SchemaProviderInterface}.
         * The configuration is an array of provider class names. Alternatively, you can specify provider class as key
         * and its config as value:
         */
        'schema-providers' => [
            \Yiisoft\Yii\Cycle\Schema\Provider\SimpleCacheSchemaProvider::class => [
                'key' => 'my-custom-cache-key'
            ],
            \Yiisoft\Yii\Cycle\Schema\Provider\FromFileSchemaProvider::class => [
                'file' => '@runtime/cycle-schema.php'
            ],
            \Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider::class,
        ],

        /**
         * {@see \Yiisoft\Yii\Cycle\Schema\Conveyor\AnnotatedSchemaConveyor} settings
         * A list of entity directories. You can use {@see \Yiisoft\Aliases\Aliases} in paths.
         */
        'annotated-entity-paths' => [
            '@src/Entity'
        ],
    ],
];
```

Read more in Cycle documentation:

- [Connect to Database](https://github.com/cycle/docs/blob/master/basic/connect.md)
- [References and Proxies](https://github.com/cycle/docs/blob/master/advanced/promise.md)
