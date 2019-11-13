<?php

use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;
use Yiisoft\Yii\Cycle\Generator\AnnotatedSchemaConveyor;
use Yiisoft\Yii\Cycle\SchemaConveyorInterface;

/**
 * @var array $params
 */

return [
    // Cycle DBAL
    DatabaseManager::class => new DbalFactory(),
    // Cycle ORM
    ORMInterface::class => new OrmFactory(),

    SchemaConveyorInterface::class => static function (ContainerInterface $container) {
        $conveyor = new AnnotatedSchemaConveyor($container);
        $conveyor->addEntityPaths($container->get(Cycle\CycleCommonConfig::class)->entityPaths);
        return $conveyor;
    },
] + (new Cycle\Config\DIConfigGenerator($params))->generate();
