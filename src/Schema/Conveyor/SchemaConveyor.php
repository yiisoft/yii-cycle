<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use Cycle\Schema\Generator;
use Cycle\Schema\GeneratorInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;
use Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException;

class SchemaConveyor implements SchemaConveyorInterface
{
    protected array $conveyor = [
        self::STAGE_INDEX => [
            Generator\ResetTables::class,       // re-declared table schemas (remove columns)
        ],
        self::STAGE_RENDER => [
            Generator\GenerateRelations::class, // generate entity relations
            Generator\ValidateEntities::class,  // make sure all entity schemas are correct
            Generator\RenderTables::class,      // declare table schemas
            Generator\RenderRelations::class,   // declare relation keys and indexes
        ],
        self::STAGE_USERLAND => [],
        self::STAGE_POSTPROCESS => [
            Generator\GenerateTypecast::class   // typecast non string columns
        ],
    ];

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addGenerator(string $stage, $generator): void
    {
        $this->conveyor[$stage][] = $generator;
    }

    public function getGenerators(): array
    {
        $result = [];
        foreach ($this->conveyor as $group) {
            foreach ($group as $generatorDefinition) {
                $generator = null;
                if (is_string($generatorDefinition)) {
                    $generator = $this->container->get($generatorDefinition);
                } elseif ($generatorDefinition instanceof GeneratorInterface) {
                    $result[] = $generatorDefinition;
                    continue;
                } elseif (is_object($generatorDefinition) && is_callable($generatorDefinition)) {
                    $generator = $generatorDefinition($this->container);
                }
                if ($generator instanceof GeneratorInterface) {
                    $result[] = $generator;
                    continue;
                }
                throw new BadGeneratorDeclarationException($generator ?? $generatorDefinition);
            }
        }
        return $result;
    }
}
