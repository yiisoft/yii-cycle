<?php

namespace Yiisoft\Yii\Cycle\Helper;

use Cycle\Migrations\GenerateMigrations;
use Cycle\Annotated;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator;
use Cycle\Schema\Registry;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Migrator;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;
use Yiisoft\Aliases\Aliases;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Yii\Cycle\Model\SchemaConveyor;
use Yiisoft\Yii\Cycle\SchemaConveyorInterface;

class CycleOrmHelper
{
    /** @var DatabaseManager $dbal */
    private $dbal;

    /** @var Aliases */
    private $aliases;

    /** @var CacheInterface */
    private $cache;

    /** @var string */
    private $cacheKey = 'Cycle-ORM-Schema';

    /** @var string[] */
    private $entityPaths = [];

    /** @var int */
    private $tableNaming = Annotated\Entities::TABLE_NAMING_SINGULAR;

    /** @var SchemaConveyorInterface */
    private $schemaConveyor;

    public function __construct(
        DatabaseManager $dbal,
        Aliases $aliases,
        CacheInterface $cache,
        SchemaConveyorInterface $schemaConveyor
    ) {
        $this->aliases = $aliases;
        $this->dbal = $dbal;
        $this->cache = $cache;
        $this->schemaConveyor = $schemaConveyor;
    }

    /**
     * @param string|string[] $paths
     */
    public function addEntityPaths($paths): void
    {
        $paths = (array)$paths;
        foreach ($paths as $path) {
            $this->entityPaths[] = $path;
        }
    }

    public function dropCurrentSchemaCache(): void
    {
        $this->cache->delete($this->cacheKey);
    }

    protected function annotateConveyor()
    {
        $classLocator = $this->getEntityClassLocator();

        // register embeddable entities
        $this->schemaConveyor->addGenerator(
            SchemaConveyor::STAGE_INDEX,
            new Annotated\Embeddings($classLocator)
        );
        // register annotated entities
        $this->schemaConveyor->addGenerator(
            SchemaConveyor::STAGE_INDEX,
            new Annotated\Entities($classLocator, null, $this->tableNaming)
        );
        // add @Table column declarations
        $this->schemaConveyor->addGenerator(SchemaConveyor::STAGE_INDEX, Annotated\MergeColumns::class);
        // add @Table(indexes) column declarations
        $this->schemaConveyor->addGenerator(SchemaConveyor::STAGE_RENDER, Annotated\MergeIndexes::class);
    }

    public function generateMigrations(Migrator $migrator, MigrationConfig $config, ?array $generators = []): void
    {
        // autoload annotations
        AnnotationRegistry::registerLoader('class_exists');

        $this->annotateConveyor();

        // add migrations generator
        $migrate = new GenerateMigrations($migrator->getRepository(), $config);
        $this->schemaConveyor->addGenerator(SchemaConveyor::STAGE_USERLAND, $migrate);

        $conveyor = $this->schemaConveyor->getConveyor();

        (new Compiler())->compile(new Registry($this->dbal), $conveyor);
    }

    public function getCurrentSchemaArray($fromCache = true): array
    {
        $getSchemaArray = function () {
            // autoload annotations
            AnnotationRegistry::registerLoader('class_exists');
            // sync table changes to database
            $this->schemaConveyor->addGenerator(SchemaConveyor::STAGE_RENDER, Generator\SyncTables::class);
            $conveyor = $this->schemaConveyor->getConveyor();
            return (new Compiler())->compile(new Registry($this->dbal), $conveyor);
        };

        if ($fromCache) {
            $schema = $this->cache->get($this->cacheKey);
            if (is_array($schema)) {
                return $schema;
            }
        }
        $schema = $getSchemaArray();
        $this->cache->set($this->cacheKey, $schema);
        return $schema;
    }

    private function getEntityClassLocator(): ClassLocator
    {
        $list = [];
        foreach ($this->entityPaths as $path) {
            $list[] = $this->aliases->get($path);
        }
        $finder = (new Finder())
            ->files()
            ->in($list);

        return new ClassLocator($finder);
    }
}
