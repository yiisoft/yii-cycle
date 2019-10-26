<?php

namespace Yiisoft\Yii\Cycle\Helper;

use Cycle\Migrations\GenerateMigrations;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator;
use Cycle\Schema\Registry;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Migrator;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Yii\Cycle\Model\SchemaConveyor;
use Yiisoft\Yii\Cycle\SchemaConveyorInterface;

class CycleOrmHelper
{
    /** @var DatabaseManager $dbal */
    private $dbal;

    /** @var CacheInterface */
    private $cache;

    /** @var string */
    private $cacheKey = 'Cycle-ORM-Schema';

    /** @var SchemaConveyorInterface */
    private $schemaConveyor;

    public function __construct(
        DatabaseManager $dbal,
        CacheInterface $cache,
        SchemaConveyorInterface $schemaConveyor
    ) {
        $this->dbal = $dbal;
        $this->cache = $cache;
        $this->schemaConveyor = $schemaConveyor;
    }

    public function dropCurrentSchemaCache(): void
    {
        $this->cache->delete($this->cacheKey);
    }

    public function generateMigrations(Migrator $migrator, MigrationConfig $config, ?array $generators = []): void
    {
        // autoload annotations
        AnnotationRegistry::registerLoader('class_exists');

        // add migrations generator
        $migrate = new GenerateMigrations($migrator->getRepository(), $config);
        $this->schemaConveyor->addGenerator(SchemaConveyor::STAGE_USERLAND, $migrate);

        $conveyor = $this->schemaConveyor->getGenerators();

        (new Compiler())->compile(new Registry($this->dbal), $conveyor);
    }

    public function getCurrentSchemaArray($fromCache = true): array
    {
        if ($fromCache) {
            $schema = $this->cache->get($this->cacheKey);
            if (is_array($schema)) {
                return $schema;
            }
        }
        // autoload annotations
        AnnotationRegistry::registerLoader('class_exists');
        // sync table changes to database
        $this->schemaConveyor->addGenerator(SchemaConveyor::STAGE_RENDER, Generator\SyncTables::class);
        // compile schema array
        $conveyor = $this->schemaConveyor->getGenerators();
        $schema = (new Compiler())->compile(new Registry($this->dbal), $conveyor);

        $this->cache->set($this->cacheKey, $schema);
        return $schema;
    }
}
