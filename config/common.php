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
        $conveyor->addEntityPaths($container->get(Cycle\CommonConfig::class)->entityPaths);
        return $conveyor;
    },

    Cycle\DbalConfig::class => [
        '__class' => Cycle\DbalConfig::class,
        'configure()' => [$params[Cycle\DbalConfig::class]],
    ],

    Cycle\CommonConfig::class => [
        '__class' => Cycle\CommonConfig::class,
        'configure()' => [$params[Cycle\CommonConfig::class]],
    ],

    Cycle\MigrationConfig::class => [
        '__class' => Cycle\MigrationConfig::class,
        'configure()' => [$params[Cycle\MigrationConfig::class]],
    ],
];
