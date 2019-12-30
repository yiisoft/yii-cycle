<?php

namespace Yiisoft\Yii\Cycle\Helper;

use Closure;
use Cycle\Migrations\GenerateMigrations;
use Cycle\Schema\Compiler;
use Cycle\Schema\GeneratorInterface;
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

    /**
     * @param Migrator $migrator
     * @param MigrationConfig $config
     * @param GeneratorInterface[]|string[]|Closure[] $generators Additional generators
     * @throws \Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException
     */
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

    /**
     * @param bool $fromCache
     * @param GeneratorInterface[]|string[]|Closure[] $generators Additional generators
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException
     */
    public function getCurrentSchemaArray($fromCache = true, array $generators = []): array
    {
        if ($fromCache) {
            $schema = $this->cache->get($this->cacheKey);
            if (is_array($schema)) {
                return $schema;
            }
        }
        // add generators to userland
        foreach ($generators as $generator) {
            $this->schemaConveyor->addGenerator($this->schemaConveyor::STAGE_USERLAND, $generator);
        }
        // compile schema array
        $conveyor = $this->schemaConveyor->getGenerators();

        $schema = (new Compiler())->compile(new Registry($this->dbal), $conveyor);

        $this->cache->set($this->cacheKey, $schema);
        return $schema;
    }

    public function setCacheKey(string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }
}
