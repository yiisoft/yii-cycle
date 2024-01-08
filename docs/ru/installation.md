# Установка

Предпочтительнее установить этот пакет через [Composer](https://getcomposer.org/download/):

```bash
composer require yiisoft/yii-cycle "2.0.x-dev"
```

## Настройка

Если вы используете Yii с плагином `composer-config-plugin`, то настройки Yii-Cycle
можете указать в файле `config/params.php`:

```php
<?php
use Cycle\Schema\Generator;
use Yiisoft\Yii\Cycle\Schema\Conveyor\AttributedSchemaConveyor;

return [
    // Общий конфиг Cycle
    'yiisoft/yii-cycle' => [
        // Конфиг Cycle DBAL
        'dbal' => [
            /**
             * Логгер SQL запросов
             * Вы можете использовать класс {@see \Yiisoft\Yii\Cycle\Logger\StdoutQueryLogger}, чтобы выводить SQL лог
             * в stdout, или любой другой PSR-совместимый логгер
             */
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
            \Yiisoft\Yii\Cycle\Schema\Provider\SimpleCacheSchemaProvider::class => [
                'key' => 'my-custom-cache-key'
            ],
            \Yiisoft\Yii\Cycle\Schema\Provider\FromFilesSchemaProvider::class => [
                'files' => ['@runtime/cycle-schema.php']
            ],
            \Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider::class,
        ],

        /**
         * Настройка для класса {@see \Yiisoft\Yii\Cycle\Schema\Conveyor\MetadataSchemaConveyor}.
         * Здесь указывается список папок с сущностями.
         * В путях поддерживаются псевдонимы {@see \Yiisoft\Aliases\Aliases}.
         */
        'entity-paths' => [
            '@src/Entity'
        ],
        /**
         * Реализация интерфейса {@see \Yiisoft\Yii\Cycle\Schema\Conveyor\SchemaConveyorInterface},
         * определяющая источник данных о сущностях:
         *  - `AttributedSchemaConveyor` - парсинг только атрибутов (в том числе и на PHP 7.4);
         *  - `AnnotatedSchemaConveyor` - парсинг только аннотаций;
         *  - `CompositeSchemaConveyor` - парсинг и аннотаций, и атрибутов.
         */
        'conveyor-class' => AttributedSchemaConveyor::class,
    ],
];
```

Документация Cycle:

- [Конфигурирование подключений](https://cycle-orm.dev/docs/database-configuration/2.x/en#installation-declare-connection)
