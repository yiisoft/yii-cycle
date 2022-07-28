<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Conveyor;

use Cycle\Schema\GeneratorInterface;
use Yiisoft\Yii\Cycle\Schema\Conveyor\SchemaConveyor;
use Yiisoft\Yii\Cycle\Tests\Schema\Conveyor\Stub\FakeGenerator;

class SchemaConveyorTest extends BaseConveyorTest
{
    public function testDefaultGeneratorsList(): void
    {
        $conveyor = $this->createConveyor();

        $generators = $this->getGeneratorClassList($conveyor);

        $this->assertSame([
            'Cycle\Schema\Generator\ResetTables',
            'Cycle\Schema\Generator\GenerateRelations',
            'Cycle\Schema\Generator\GenerateModifiers',
            'Cycle\Schema\Generator\ValidateEntities',
            'Cycle\Schema\Generator\RenderTables',
            'Cycle\Schema\Generator\RenderRelations',
            'Cycle\Schema\Generator\RenderModifiers',
            'Cycle\Schema\Generator\GenerateTypecast',
        ], $generators);
    }

    public function testAddCustomGenerators(): void
    {
        $conveyor = $this->createConveyor();
        $conveyor->addGenerator(
            $conveyor::STAGE_POSTPROCESS,
            new class () {
                public function __invoke(): GeneratorInterface
                {
                    return new FakeGenerator('FakeGenerator-from-invocable-object');
                }
            }
        );
        $conveyor->addGenerator($conveyor::STAGE_USERLAND, static function () {
            return new FakeGenerator('FakeGenerator-from-closure');
        });
        $conveyor->addGenerator($conveyor::STAGE_RENDER, \Cycle\Schema\Generator\SyncTables::class);
        $conveyor->addGenerator($conveyor::STAGE_INDEX, new FakeGenerator('FakeGenerator-object'));

        // get generators list
        /** @var string[] $generators */
        $generators = array_map(
            fn ($value) => $value instanceof FakeGenerator ? $value->originClass() : get_class($value),
            $conveyor->getGenerators()
        );

        $this->assertSame([
            'Cycle\Schema\Generator\ResetTables',
            'FakeGenerator-object',
            'Cycle\Schema\Generator\GenerateRelations',
            'Cycle\Schema\Generator\GenerateModifiers',
            'Cycle\Schema\Generator\ValidateEntities',
            'Cycle\Schema\Generator\RenderTables',
            'Cycle\Schema\Generator\RenderRelations',
            'Cycle\Schema\Generator\RenderModifiers',
            \Cycle\Schema\Generator\SyncTables::class,
            'FakeGenerator-from-closure',
            'Cycle\Schema\Generator\GenerateTypecast',
            'FakeGenerator-from-invocable-object',
        ], $generators);
    }

    public function testAddCustomGeneratorObject(): void
    {
        $conveyor = $this->createConveyor();
        $conveyor->addGenerator($conveyor::STAGE_POSTPROCESS, \Cycle\Schema\Generator\GenerateTypecast::class);
        $conveyor->addGenerator($conveyor::STAGE_USERLAND, \Cycle\Schema\Generator\RenderTables::class);
        $conveyor->addGenerator($conveyor::STAGE_RENDER, \Cycle\Schema\Generator\SyncTables::class);
        $conveyor->addGenerator($conveyor::STAGE_INDEX, \Cycle\Annotated\MergeIndexes::class);

        $generators = $this->getGeneratorClassList($conveyor);

        $this->assertSame([
            'Cycle\Schema\Generator\ResetTables',
            \Cycle\Annotated\MergeIndexes::class,
            'Cycle\Schema\Generator\GenerateRelations',
            'Cycle\Schema\Generator\GenerateModifiers',
            'Cycle\Schema\Generator\ValidateEntities',
            'Cycle\Schema\Generator\RenderTables',
            'Cycle\Schema\Generator\RenderRelations',
            'Cycle\Schema\Generator\RenderModifiers',
            \Cycle\Schema\Generator\SyncTables::class,
            \Cycle\Schema\Generator\RenderTables::class,
            'Cycle\Schema\Generator\GenerateTypecast',
            \Cycle\Schema\Generator\GenerateTypecast::class,
        ], $generators);
    }

    public function createConveyor(): SchemaConveyor
    {
        return new SchemaConveyor($this->prepareContainer());
    }
}
