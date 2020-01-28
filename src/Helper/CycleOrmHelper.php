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
    private SchemaConveyorInterface $schemaConveyor;

    public function __construct(
        DatabaseManager $dbal,
        SchemaConveyorInterface $schemaConveyor
    ) {
        $this->dbal = $dbal;
        $this->schemaConveyor = $schemaConveyor;
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

}
