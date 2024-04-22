# Установка

Предпочтительнее установить этот пакет через [Composer](https://getcomposer.org/download/):

```shell
composer require yiisoft/yii-cycle
```

## Настройка

Если вы используете Yii с плагином `composer-config-plugin`, то настройки Yii-Cycle
можете указать в файле `config/params.php`:

```php
<?php
use Cycle\Schema\Generator;
use Cycle\Schema\Provider\FromFilesSchemaProvider;
use Cycle\Schema\Provider\SimpleCacheSchemaProvider;
use Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider;

return [
    // Общий конфиг Cycle
    'yiisoft/yii-cycle' => [
        // Конфиг Cycle DBAL
        'dbal' => [
            // PSR-3 совместимый логгер SQL запросов
            'query-logger' => null,
            // БД по умолчанию (из списка 'databases')
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'sqlite']
            ],
            'connections' => [
                // Пример настроек подключения к SQLite:
                'sqlite' => new \Cycle\Database\Config\SQLiteDriverConfig(
                    connection: new \Cycle\Database\Config\SQLite\DsnConnectionConfig(
                        // Синтаксис DSN описан в https://www.php.net/manual/pdo.construct.php
                        dsn: 'sqlite:runtime/database.db'
                    )
                ),
            ],
        ],

        // Конфиг миграций
        'migrations' => [
            'directory' => '@root/migrations',
            'namespace' => 'App\\Migration',
            'table' => 'migration',
            'safe' => false,
        ],

        /**
         * Список поставщиков схемы БД для {@see \Yiisoft\Yii\Cycle\Schema\Provider\Support\SchemaProviderPipeline}
         * Поставщики схемы реализуют класс {@see SchemaProviderInterface}.
         * Конфигурируется перечислением имён классов поставщиков. Вы здесь можете конфигурировать также и поставщиков,
         * указывая имя класса поставщика в качестве ключа элемента, а конфиг в виде массива элемента:
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
         * Настройка для класса {@see \Yiisoft\Yii\Cycle\Schema\Conveyor\MetadataSchemaConveyor}.
         * Здесь указывается список папок с сущностями.
         * В путях поддерживаются псевдонимы {@see \Yiisoft\Aliases\Aliases}.
         */
        'entity-paths' => [
            '@src/Entity'
        ],
    ],
];
```

Документация Cycle:

- [Конфигурирование подключений](https://cycle-orm.dev/docs/database-configuration/2.x/en#installation-declare-connection)
