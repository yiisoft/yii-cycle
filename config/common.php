<?php

declare(strict_types=1);

use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;
use Yiisoft\Yii\Cycle\Schema\Conveyor\AnnotatedSchemaConveyor;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaManager;

/**
 * @var array $params
 */

return [
    // Cycle DBAL
    DatabaseManager::class => new DbalFactory($params['yiisoft/yii-cycle']['dbal']),
    // Cycle ORM
    ORMInterface::class => new OrmFactory($params['yiisoft/yii-cycle']['orm-promise-factory']),
    // Factory for Cycle ORM
    FactoryInterface::class => static function (ContainerInterface $container) {
        return new Factory($container->get(DatabaseManager::class), null, null, $container);
    },
    // Schema Provider dispatcher
    SchemaManager::class => static function (ContainerInterface $container) use (&$params) {
        return new SchemaManager($container, $params['yiisoft/yii-cycle']['schema-providers']);
    },
    // Schema
    SchemaInterface::class => static function (ContainerInterface $container) {
        $schema = $container->get(SchemaManager::class)->read();
        if ($schema === null) {
            throw new RuntimeException('Cycle Schema not read.');
        }
        return new Schema($schema);
    },
    // Annotated Schema Conveyor
    SchemaConveyorInterface::class => static function (ContainerInterface $container) use (&$params) {
        $conveyor = new AnnotatedSchemaConveyor($container);
        $conveyor->addEntityPaths($params['yiisoft/yii-cycle']['annotated-entity-paths']);
        return $conveyor;
    },
];
