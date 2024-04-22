# Installation

The preferred way to install this package is through [Composer](https://getcomposer.org/download/):

```shell
composer require yiisoft/yii-cycle
```

## Configuring package

If you use Yii with `composer-config-plugin`, Yii-Cycle settings could be specified in `config/params.php`:

```php
use Cycle\Schema\Generator;
use Cycle\Schema\Provider\FromFilesSchemaProvider;
use Cycle\Schema\Provider\SimpleCacheSchemaProvider;
use Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider;

return [
    // Common Cycle config
    'yiisoft/yii-cycle' => [
        // Cycle DBAL config
        'dbal' => [
             // PSR-3 compatible SQL query logger
            'query-logger' => null,
            // Default database (from 'databases' list)
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'sqlite']
            ],
            'connections' => [
                // Example SQLite connection:
                'sqlite' => new \Cycle\Database\Config\SQLiteDriverConfig(
                    connection: new \Cycle\Database\Config\SQLite\DsnConnectionConfig(
                        // see https://www.php.net/manual/pdo.construct.php, DSN for connection syntax
                        dsn: 'sqlite:runtime/database.db'
                    )
                ),
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
         * A list of DB schema providers for {@see \Yiisoft\Yii\Cycle\Schema\Provider\Support\SchemaProviderPipeline}
         * Providers are implementing {@see SchemaProviderInterface}.
         * The configuration is an array of provider class names. Alternatively, you can specify provider class as key
         * and its config as value:
         */
        'schema-providers' => [
            SimpleCacheSchemaProvider::class => SimpleCacheSchemaProvider::config(
                key: 'my-custom-cache-key'
            ),
            FromFilesSchemaProvider::class => FromFilesSchemaProvider::config(
                files: ['@runtime/cycle-schema.php'],
            ),
            FromConveyorSchemaProvider::class,
        ],

        /**
         * Option for {@see \Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider}.
         * A list of entity directories. You can use {@see \Yiisoft\Aliases\Aliases} in paths.
         */
        'entity-paths' => [
            '@src/Entity'
        ],
    ],
];
```

Read more in Cycle documentation:

- [Connect to Database](https://cycle-orm.dev/docs/database-configuration/2.x/en#installation-declare-connection)
