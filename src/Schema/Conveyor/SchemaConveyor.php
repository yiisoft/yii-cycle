<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use Cycle\Schema\Generator;
use Cycle\Schema\GeneratorInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;

abstract class SchemaConveyor implements SchemaConveyorInterface
{
    protected array $conveyor = [
        self::STAGE_INDEX => [
            Generator\ResetTables::class,       // re-declared table schemas (remove columns)
        ],
        self::STAGE_RENDER => [
            Generator\GenerateRelations::class, // generate entity relations
            Generator\GenerateModifiers::class, // generate changes from schema modifiers
            Generator\ValidateEntities::class,  // make sure all entity schemas are correct
            Generator\RenderTables::class,      // declare table schemas
            Generator\RenderRelations::class,   // declare relation keys and indexes
            Generator\RenderModifiers::class,   // render all schema modifiers
            Generator\ForeignKeys::class,       // define foreign key constraints
        ],
        self::STAGE_USERLAND => [],
        self::STAGE_POSTPROCESS => [
            Generator\GenerateTypecast::class,   // typecast non string columns
        ],
    ];

    public function __construct(protected ContainerInterface $container)
    {
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
