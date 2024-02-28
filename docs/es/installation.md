# Instalación

La forma preferida de instalar este paquete es a través de [Composer](https://getcomposer.org/download/):

```bash
composer require yiisoft/yii-cycle
```

## Configurar paquete

Si utiliza `yiisoft/config`, la configuración de `yisoft/yii-cycle` se debe especificar en `config/params.php`:

```php
<?php
use Cycle\Schema\Generator;
use Cycle\Schema\Provider\FromFilesSchemaProvider;
use Cycle\Schema\Provider\SimpleCacheSchemaProvider;
use Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider;

return [
    // Configuración de Cycle común
    'yiisoft/yii-cycle' => [
        // Configuración de Cycle DBAL
        'dbal' => [
            // PSR-3 SQL query logger
            'query-logger' => null,
            // Bases de datos por defecto (De la lista 'databases')
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'sqlite']
            ],
            'connections' => [
                // Ejemplo de conexión a SQLite:
                'sqlite' => new \Cycle\Database\Config\SQLiteDriverConfig(
                    connection: new \Cycle\Database\Config\SQLite\DsnConnectionConfig(
                        // Lee https://www.php.net/manual/pdo.construct.php, para la sintaxis de conexión DSN.
                        dsn: 'sqlite:runtime/database.db'
                    )
                ),
            ],
        ],

        // Configuración de las migraciones
        'migrations' => [
            'directory' => '@root/migrations',
            'namespace' => 'App\\Migration',
            'table' => 'migration',
            'safe' => false,
        ],

        /**
         * Una lista de proveedores de esquemas de BD para {@see \Yiisoft\Yii\Cycle\Schema\Provider\Support\SchemaProviderPipeline}
         * Los Proveedores están implementando {@see SchemaProviderInterface}.
         * La configuración es un array de classNames de proveedores. Como alternativa, puede especificar la clase de proveedor como clave
         * y su configuración como valor:
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
         * Opción para {@see \Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider}.
         * Una lista de directorios de entidades. Puede utilizar {@see \Yiisoft\Aliases\Aliases} en las rutas.
         */
        'entity-paths' => [
            '@src/Entity'
        ],
    ],
];
```

Más información en la documentación de Cycle:

- [Conectar a una base de datos](https://cycle-orm.dev/docs/database-configuration/2.x/en#installation-declare-connection)
