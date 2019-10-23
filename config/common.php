<?php

use Cycle\ORM\ORMInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;

/**
 * @var array $params
 */

return [
    // Cycle DBAL
    DatabaseManager::class => new DbalFactory($params['cycle.dbal']),
    // Cycle ORM
    ORMInterface::class => new OrmFactory(),
    // Cycle Entity Finder
    CycleOrmHelper::class => [
        '__class' => CycleOrmHelper::class,
        'addEntityPaths()' => [
            'paths' => $params['cycle.locator']['entityPaths'],
        ],
    ],

];
