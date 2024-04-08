# Instalação

A forma preferida de instalar este pacote é através do [Composer](https://getcomposer.org/download/):

```bash
compositor requer yiisoft/yii-cycle
```

## Configurando o pacote

Se você usa Yii com `composer-config-plugin`, as configurações do Yii-Cycle podem ser especificadas em `config/params.php`:

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

Leia mais na documentação do Cycle:

- [Conectar ao banco de dados](https://cycle-orm.dev/docs/database-configuration/2.x/en#installation-declare-connection)
