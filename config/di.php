<?php

declare(strict_types=1);

use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\Entity\Behavior\EventDrivenCommandGenerator as BehaviorsHandler;
use Cycle\ORM\EntityManager;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\FactoryInterface as CycleFactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Provider\FromFilesSchemaProvider;
use Cycle\Schema\Provider\PhpFileSchemaProvider;
use Cycle\Schema\Provider\SchemaProviderInterface;
use Cycle\Schema\Provider\Support\SchemaProviderPipeline;
use Psr\Container\ContainerInterface;
use Spiral\Core\FactoryInterface as SpiralFactoryInterface;
use Spiral\Files\FilesInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\Yii\Cycle\Exception\SchemaWasNotProvidedException;
use Yiisoft\Yii\Cycle\Factory\CycleDynamicFactory;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;
use Yiisoft\Yii\Cycle\Schema\Conveyor\MetadataSchemaConveyor;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;

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
    EntityManager::class => [
        'reset' => function () {
            $this->clean();
        },
    ],

    // Spiral Core Factory
    SpiralFactoryInterface::class => Reference::to(CycleDynamicFactory::class),

    // Factory for Cycle ORM
    // todo: move to separated class
    CycleFactoryInterface::class => new OrmFactory($params['yiisoft/yii-cycle']['collections'] ?? []),

    // Schema
    SchemaInterface::class => static fn (SchemaProviderInterface $schemaProvider): SchemaInterface => new Schema(
        $schemaProvider->read() ?? throw new SchemaWasNotProvidedException()
    ),

    // Schema provider
    SchemaProviderInterface::class => static function (ContainerInterface $container) use (&$params) {
        return (new SchemaProviderPipeline($container))->withConfig($params['yiisoft/yii-cycle']['schema-providers']);
    },

    // FromFilesSchemaProvider
    FromFilesSchemaProvider::class => static function (Aliases $aliases) {
        return new FromFilesSchemaProvider(static fn (string $path): string => $aliases->get($path));
    },

    // PhpFileSchemaProvider
    PhpFileSchemaProvider::class => [
        '__construct()' => [
            DynamicReference::to(
                static fn (Aliases $aliases): \Closure => static fn (string $path): string => $aliases->get($path)
            ),
            Reference::optional(FilesInterface::class),
        ],
    ],

    // Schema Conveyor
    SchemaConveyorInterface::class => [
        'class' => MetadataSchemaConveyor::class,
        'addEntityPaths()' => $params['yiisoft/yii-cycle']['entity-paths'] ?? [],
    ],
];
