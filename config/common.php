<?php

declare(strict_types=1);

use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\Collection\CollectionFactoryInterface;
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
use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;
use Yiisoft\Yii\Cycle\Exception\ConfigException;
use Yiisoft\Yii\Cycle\Exception\SchemaWasNotProvidedException;
use Yiisoft\Yii\Cycle\Factory\CycleDynamicFactory;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Schema\Conveyor\CompositeSchemaConveyor;
use Yiisoft\Yii\Cycle\Schema\Conveyor\MetadataSchemaConveyor;
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
    ORM::class => static fn (
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
    CycleFactoryInterface::class => static function (
        DatabaseManager $dbal,
        SpiralFactoryInterface $factory,
        Injector $injector
    ) use (&$params): CycleFactoryInterface {
        // Manage collection factory list
        $cfgPath = ['yiisoft/yii-cycle', 'collections'];
        try {
            $config = $params['yiisoft/yii-cycle']['collections'] ?? [];
            // Resolve collection factories
            $factories = [];
            foreach ($config['factories'] ?? [] as $alias => $definition) {
                $factories[$alias] = $injector->make($definition);
                if (!$factories[$alias] instanceof CollectionFactoryInterface) {
                    $cfgPath[] = 'factories';
                    throw new BadDeclarationException(
                        "Collection factory `$alias`",
                        CollectionFactoryInterface::class,
                        $factories[$alias]
                    );
                }
            }

            // Resolve default collection factory
            $default = $config['default'] ?? null;
            if ($default !== null) {
                if (!\array_key_exists($default, $factories)) {
                    if (!\is_a($default, CollectionFactoryInterface::class, true)) {
                        $cfgPath[] = 'default';
                        throw new \RuntimeException(\sprintf('Default collection factory `%s` not found.', $default));
                    }
                    $default = \is_string($default) ? $injector->make($default) : $default;
                } else {
                    $default = $factories[$default];
                }
            }
        } catch (\Throwable $e) {
            throw new ConfigException($cfgPath, $e->getMessage(), 0, $e);
        }

        $result = new Factory($dbal, null, $factory, $default);
        // attach collection factories
        foreach ($factories as $alias => $collectionFactory) {
            $result = $result->withCollectionFactory($alias, $collectionFactory);
        }
        return $result;
    },

    // Schema
    SchemaInterface::class => static fn (SchemaProviderInterface $schemaProvider): SchemaInterface => new Schema(
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

        if ($conveyor instanceof MetadataSchemaConveyor) {
            // deprecated option
            if (\array_key_exists('annotated-entity-paths', $params['yiisoft/yii-cycle'])) {
                $conveyor->addEntityPaths($params['yiisoft/yii-cycle']['annotated-entity-paths']);
            }
            // actual option
            if (\array_key_exists('entity-paths', $params['yiisoft/yii-cycle'])) {
                $conveyor->addEntityPaths($params['yiisoft/yii-cycle']['entity-paths']);
            }
        }
        return $conveyor;
    },
];
