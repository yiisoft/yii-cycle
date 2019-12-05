<?php

namespace Yiisoft\Yii\Cycle\Helper;

use Cycle\Migrations\GenerateMigrations;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator;
use Cycle\Schema\Registry;
use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Migrator;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Yii\Cycle\SchemaConveyorInterface;

class CycleOrmHelper
{
    private DatabaseManager $dbal;

    private CacheInterface $cache;

    private string $cacheKey = 'Cycle-ORM-Schema';

    private SchemaConveyorInterface $schemaConveyor;

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

    public function generateMigrations(Migrator $migrator, MigrationConfig $config, array $generators = []): void
    {
        // add migrations generator
        $migrate = new GenerateMigrations($migrator->getRepository(), $config);
        $this->schemaConveyor->addGenerator($this->schemaConveyor::STAGE_USERLAND, $migrate);
        // add custom generators
        foreach ($generators as $generator) {
            $this->schemaConveyor->addGenerator($this->schemaConveyor::STAGE_USERLAND, $generator);
        }

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
        // sync table changes to database
        $this->schemaConveyor->addGenerator($this->schemaConveyor::STAGE_RENDER, Generator\SyncTables::class);
        // compile schema array
        $conveyor = $this->schemaConveyor->getGenerators();
        $schema = (new Compiler())->compile(new Registry($this->dbal), $conveyor);

        $this->cache->set($this->cacheKey, $schema);
        return $schema;
    }
}
