<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii Cycle ORM support</h1>
    <br>
</p>

WIP

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii-cycle/v/stable.png)](https://packagist.org/packages/yiisoft/yii-cycle)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii-cycle/downloads.png)](https://packagist.org/packages/yiisoft/yii-cycle)
[![Build Status](https://travis-ci.com/yiisoft/yii-cycle.svg?branch=master)](https://travis-ci.com/yiisoft/yii-cycle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/yii-cycle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/yii-cycle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/yii-cycle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/yii-cycle/?branch=master)

## Configuration

Specify config file in `params` section for `composer-config-plugin`
```php
<?php
use Cycle\Schema\Generator;

return [
    // cycle DBAL config
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

    // cycle common config
    'cycle.common' => [
        'entityPaths' => [
            '@src/Entity'
        ],
        'cacheKey' => 'Cycle-ORM-Schema',
        'generators' => [
            // sync table changes to database
            Generator\SyncTables::class,
        ],
        // cycle/proxy-factory extension required
        'promiseFactory' => \Cycle\ORM\Promise\ProxyFactory::class,
        // \Psr\Log\LoggerInterface definition
        'queryLogger' => null,
    ],

    // cycle migration config
    'cycle.migrations' => [
        'directory' => '@root/migrations',
        'namespace' => 'App\\Migration',
        'table' => 'migration',
        'safe' => false,
    ],
];
```
[About DBAL connections configuration](https://github.com/cycle/docs/blob/master/basic/connect.md)

## Documentation

[English](docs/guide-en/README.md) \
[Russian](docs/guide-ru/README.md)

## Commands

```bash
cycle/schema
migrate/list
migrate/create
migrate/generate
migrate/up
migrate/down
```
