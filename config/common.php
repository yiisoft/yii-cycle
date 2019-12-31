<?php

use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Factory\HelperFactory;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;
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
    ORMInterface::class => new OrmFactory($params['cycle.common']),
    // Annotated Schema Conveyor
    SchemaConveyorInterface::class => static function (ContainerInterface $container) use (&$params) {
        $conveyor = new AnnotatedSchemaConveyor($container);
        $conveyor->addEntityPaths($params['cycle.common']['entityPaths']);
        return $conveyor;
    },

    CycleOrmHelper::class => new HelperFactory($params['cycle.common']),
];
