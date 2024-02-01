<?php

declare(strict_types=1);

use Cycle\Schema\Provider\SchemaProviderInterface;
use Cycle\Schema\Provider\Support\SchemaProviderPipeline;
use Yiisoft\Yii\Cycle\Command\Schema;
use Yiisoft\Yii\Cycle\Command\Migration;
use Yiisoft\Yii\Cycle\Schema\Conveyor\CompositeSchemaConveyor;

return [
    // Console commands
    'yiisoft/yii-console' => [
        'commands' => [
            'cycle/schema' => Schema\SchemaCommand::class,
            'cycle/schema/php' => Schema\SchemaPhpCommand::class,
            'cycle/schema/clear' => Schema\SchemaClearCommand::class,
            'cycle/schema/rebuild' => Schema\SchemaRebuildCommand::class,
            'migrate/create' => Migration\CreateCommand::class,
            'migrate/generate' => Migration\GenerateCommand::class,
            'migrate/up' => Migration\UpCommand::class,
            'migrate/down' => Migration\DownCommand::class,
            'migrate/list' => Migration\ListCommand::class,
        ],
    ],

    'yiisoft/yii-cycle' => [
        // DBAL config
        'dbal' => [
            // SQL query logger. Definition of Psr\Log\LoggerInterface
            'query-logger' => null,
            // Default database
            'default' => null,
            'aliases' => [],
            'databases' => [],
            'connections' => [],
        ],

        // Cycle migration config
        'migrations' => [
            'directory' => '@root/migrations',
            'namespace' => 'App\\Migration',
            'table' => 'migration',
            'safe' => false,
        ],

        /**
         * SchemaProvider list for {@see SchemaProviderPipeline}
         * Array of classname and {@see SchemaProviderInterface} object.
         * You can configure providers if you pass classname as key and parameters as array:
         * [
         *     SimpleCacheSchemaProvider::class => [
         *         'key' => 'my-custom-cache-key'
         *     ],
         *     FromFilesSchemaProvider::class => [
         *         'files' => ['@runtime/cycle-schema.php']
         *     ],
         *     FromConveyorSchemaProvider::class => [
         *         'generators' => [
         *              Generator\SyncTables::class, // sync table changes to database
         *          ]
         *     ],
         * ]
         */
        'schema-providers' => [],

        /**
         * Collection factories.
         *
         * @link https://cycle-orm.dev/docs/relation-collections/2.x
         */
        'collections' => [
            /** Default factory (class or name from the `factories` list below) or {@see null} */
            'default' => 'array',
            /** List of class names that implement {@see \Cycle\ORM\Collection\CollectionFactoryInterface} */
            'factories' => [
                'array' => \Cycle\ORM\Collection\ArrayCollectionFactory::class,
                // 'doctrine' => \Cycle\ORM\Collection\DoctrineCollectionFactory::class,
                // 'illuminate' => \Cycle\ORM\Collection\IlluminateCollectionFactory::class,
            ],
        ],

        /**
         * Annotated/attributed entity directories list.
         * {@see \Yiisoft\Aliases\Aliases} are also supported.
         */
        'entity-paths' => [],
        'conveyor' => CompositeSchemaConveyor::class,

        /** @deprecated use `entity-paths` key instead */
        'annotated-entity-paths' => [],
    ],
];
