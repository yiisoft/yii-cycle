# Установка

Предпочтительнее установить это расширение через [composer](http://getcomposer.org/download/):

```
composer require yiisoft/yii-cycle
```

## Настройка расширения

Если вы используете Yii с плагином `composer-config-plugin`, то настройки Yii-Cycle
можете указать в файле `config/params.php`:

```php
<?php
use Cycle\Schema\Generator;

return [
    # Конфиг Cycle DBAL:
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

    # Общий конфиг Cycle
    'cycle.common' => [
        # Список путей к папкам с файлами миграций
        'entityPaths' => [
            '@src/Entity'
        ],

        # Включить использование кеша при получении схемы БД
        'cacheEnabled' => true,
        # Ключ, используемый при кешировании схемы
        'cacheKey' => 'Cycle-ORM-Schema',

        # Дополнительные генераторы, запускаемые при расчёте схемы
        # Массив определений \Cycle\Schema\GeneratorInterface
        'generators' => [
            # Генератор SyncTables позволяет без миграций вносить изменения схемы в БД
            // \Cycle\Schema\Generator\SyncTables::class,
        ],

        # Определение класса \Cycle\ORM\PromiseFactoryInterface
        'promiseFactory' => null, # использовать объекты Promise
        # Для использования фабрики ProxyFactory необходимо подключить пакет cycle/proxy-factory
        // 'promiseFactory' => \Cycle\ORM\Promise\ProxyFactory::class,

        # Логгер SQL запросов
        # Определение класса \Psr\Log\LoggerInterface
        'queryLogger' => null,
        # Вы можете использовать класс \Yiisoft\Yii\Cycle\Logger\StdoutQueryLogger
        # чтобы выводить SQL лог в stdout
        // 'queryLogger' => \Yiisoft\Yii\Cycle\Logger\StdoutQueryLogger::class,
    ],

    # Конфиг миграций
    'cycle.migrations' => [
        'directory' => '@root/migrations',
        'namespace' => 'App\\Migration',
        'table' => 'migration',
        'safe' => false,
    ],
];
```

Документация Cycle:

- [Конфигурирование подключений](https://github.com/cycle/docs/blob/master/basic/connect.md)
- [О Reference и Proxy](https://github.com/cycle/docs/blob/master/advanced/promise.md)
