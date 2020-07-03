<?php

use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;
use Yiisoft\Yii\Cycle\Factory\SchemaFromConveyorFactory;
use Yiisoft\Yii\Cycle\Schema\Conveyor\AnnotatedSchemaConveyor;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;

/**
 * @var array $params
 */

return [
    // Cycle DBAL
    DatabaseManager::class => new DbalFactory($params['cycle.dbal'], $params['cycle.common']['queryLogger']),
    // Cycle ORM
    ORMInterface::class => new OrmFactory($params['cycle.common']['promiseFactory']),
    // Factory for Cycle ORM
    FactoryInterface::class => function (ContainerInterface $container) {
        return new Factory($container->get(DatabaseManager::class), null, null, $container);
    },
    // Schema from generators
    SchemaInterface::class => new SchemaFromConveyorFactory(
        $params['cycle.common']['cacheEnabled'],
        $params['cycle.common']['cacheKey'],
        $params['cycle.common']['generators']
    ),
    // Annotated Schema Conveyor
    SchemaConveyorInterface::class => static function (ContainerInterface $container) use (&$params) {
        $conveyor = new AnnotatedSchemaConveyor($container);
        $conveyor->addEntityPaths($params['cycle.common']['entityPaths']);
        return $conveyor;
    },
];
