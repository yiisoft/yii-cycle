<?php

use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;
use Yiisoft\Yii\Cycle\Model\SchemaConveyor;
use Yiisoft\Yii\Cycle\SchemaConveyorInterface;

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
            'paths' => $params['cycle.common']['entityPaths'],
        ],
    ],
    SchemaConveyorInterface::class => function (ContainerInterface $container) {
        return new SchemaConveyor($container);
    }

];
