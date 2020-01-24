<?php

use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Factory\HelperFactory;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;
use Yiisoft\Yii\Cycle\Factory\SchemaFromGeneratorFactory;
use Yiisoft\Yii\Cycle\Generator\AnnotatedSchemaConveyor;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;
use Yiisoft\Yii\Cycle\SchemaConveyorInterface;

/**
 * @var array $params
 */

return [
    // Cycle DBAL
    DatabaseManager::class => new DbalFactory($params['cycle.dbal']),
    // Cycle ORM
    ORMInterface::class => new OrmFactory($params['cycle.common']['promiseFactory']),
    // Factory for Cycle ORM
    FactoryInterface::class => function (ContainerInterface $c) {
        return new Factory($c->get(DatabaseManager::class));
    },
    // Schema from generators
    SchemaInterface::class => new SchemaFromGeneratorFactory(
        $params['cycle.common']['cacheEnabled'],
        $params['cycle.common']['cacheKey']
    ),

    // Annotated Schema Conveyor
    SchemaConveyorInterface::class => static function (ContainerInterface $container) use (&$params) {
        $conveyor = new AnnotatedSchemaConveyor($container);
        $conveyor->addEntityPaths($params['cycle.common']['entityPaths']);
        // add generators to userland
        foreach ($params['cycle.common']['generators'] as $generator) {
            $conveyor->addGenerator(SchemaConveyorInterface::STAGE_USERLAND, $generator);
        }
        return $conveyor;
    },

    CycleOrmHelper::class => new HelperFactory($params['cycle.common']),
];
