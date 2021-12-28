<?php

declare(strict_types=1);

use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\Entity\Behavior\EventDrivenCommandGenerator as BehaviorsHandler;
use Cycle\ORM\EntityManager;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface as CycleFactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\FactoryInterface as SpiralFactoryInterface;
use Yiisoft\Definitions\Reference;
use Yiisoft\Yii\Cycle\Exception\SchemaWasNotProvidedException;
use Yiisoft\Yii\Cycle\Factory\CycleDynamicFactory;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Schema\Conveyor\CompositeSchemaConveyor;
use Yiisoft\Yii\Cycle\Schema\Provider\Support\SchemaProviderPipeline;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

/**
 * @var array $params
 */

return [
    // Cycle DBAL
    DatabaseProviderInterface::class => Reference::to(DatabaseManager::class),
    DatabaseManager::class => new DbalFactory($params['yiisoft/yii-cycle']['dbal']),

    // Cycle ORM
    ORMInterface::class => Reference::to(ORM::class),
    ORM::class => static fn(
        CycleFactoryInterface $factory,
        SchemaInterface $schema,
        ContainerInterface $container
    ) => new ORM(
        $factory,
        $schema,
        \class_exists(BehaviorsHandler::class) ? new BehaviorsHandler($schema, $container) : null
    ),

    // Entity Manager
    EntityManagerInterface::class => Reference::to(EntityManager::class),

    // Spiral Core Factory
    SpiralFactoryInterface::class => Reference::to(CycleDynamicFactory::class),

    // Factory for Cycle ORM
    CycleFactoryInterface::class => static function (DatabaseManager $dbal, SpiralFactoryInterface $factory) {
        return new Factory($dbal, null, $factory);
    },

    // Schema
    SchemaInterface::class => static fn(SchemaProviderInterface $schemaProvider) => new Schema(
        $schemaProvider->read() ?? throw new SchemaWasNotProvidedException()
    ),

    // Schema provider
    SchemaProviderInterface::class => static function (ContainerInterface $container) use (&$params) {
        return (new SchemaProviderPipeline($container))->withConfig($params['yiisoft/yii-cycle']['schema-providers']);
    },

    // Schema Conveyor
    SchemaConveyorInterface::class => static function (ContainerInterface $container) use (&$params) {
        /** @var SchemaConveyorInterface $conveyor */
        $conveyor = $container->get($params['yiisoft/yii-cycle']['conveyor'] ?? CompositeSchemaConveyor::class);
        if (\array_key_exists('annotated-entity-paths', $params['yiisoft/yii-cycle'])) {
            $conveyor->addEntityPaths($params['yiisoft/yii-cycle']['annotated-entity-paths']);
        }
        if (\array_key_exists('entity-paths', $params['yiisoft/yii-cycle'])) {
            $conveyor->addEntityPaths($params['yiisoft/yii-cycle']['entity-paths']);
        }
        return $conveyor;
    },
];
