<?php

namespace Yiisoft\Yii\Cycle\Model;

use Cycle\Annotated;
use Cycle\Migrations\GenerateMigrations;
use Cycle\Schema\Generator;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\GeneratorInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\SchemaConveyorInterface;

class SchemaConveyor implements SchemaConveyorInterface
{
    /** @var array */
    protected $conveyor = [];
    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->conveyor = [
            self::STAGE_INDEX => [],
            self::STAGE_RENDER => [
                // render tables and relations
                Generator\ResetTables::class,       // re-declared table schemas (remove columns)
                Generator\GenerateRelations::class, // generate entity relations
                Generator\ValidateEntities::class,  // make sure all entity schemas are correct
                Generator\RenderTables::class,      // declare table schemas
                Generator\RenderRelations::class,   // declare relation keys and indexes
            ],
            self::STAGE_USERLAND => [],
            self::STAGE_POSTPROCESS => [
                // post processing
                Generator\GenerateTypecast::class   // typecast non string columns
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function addGenerator(string $stage, $generator): void
    {
        $this->conveyor[$stage][] = $generator;
    }

    /**
     * @inheritDoc
     */
    public function getConveyor(): array
    {
        $result = [];
        foreach ($this->conveyor as $group) {
            foreach ($group as $generator) {
                if (is_object($generator) && $generator instanceof GeneratorInterface) {
                    $result[] = $generator;
                } else {
                    $result[] = $this->container->get($generator);
                }
            }
        }
        return $result;
    }
}
