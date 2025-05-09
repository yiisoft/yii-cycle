<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Conveyor;

use Cycle\Annotated\MergeIndexes;
use Cycle\Schema\Generator\ForeignKeys;
use Cycle\Schema\Generator\GenerateModifiers;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\RenderModifiers;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\GeneratorInterface;
use Yiisoft\Yii\Cycle\Schema\Conveyor\SchemaConveyor;
use Yiisoft\Yii\Cycle\Tests\Feature\Schema\Conveyor\Stub\FakeGenerator;

class SchemaConveyorTest extends BaseConveyor
{
    public function testDefaultGeneratorsList(): void
    {
        $conveyor = $this->createConveyor();

        $generators = $this->getGeneratorClassList($conveyor);

        $this->assertSame([
            ResetTables::class,
            GenerateRelations::class,
            GenerateModifiers::class,
            ValidateEntities::class,
            RenderTables::class,
            RenderRelations::class,
            RenderModifiers::class,
            ForeignKeys::class,
            GenerateTypecast::class,
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
        $conveyor->addGenerator($conveyor::STAGE_USERLAND, static fn() => new FakeGenerator('FakeGenerator-from-closure'));
        $conveyor->addGenerator($conveyor::STAGE_RENDER, SyncTables::class);
        $conveyor->addGenerator($conveyor::STAGE_INDEX, new FakeGenerator('FakeGenerator-object'));

        // get generators list
        /** @var string[] $generators */
        $generators = array_map(
            fn ($value) => $value instanceof FakeGenerator ? $value->originClass() : $value::class,
            $conveyor->getGenerators()
        );

        $this->assertSame([
            ResetTables::class,
            'FakeGenerator-object',
            GenerateRelations::class,
            GenerateModifiers::class,
            ValidateEntities::class,
            RenderTables::class,
            RenderRelations::class,
            RenderModifiers::class,
            ForeignKeys::class,
            SyncTables::class,
            'FakeGenerator-from-closure',
            GenerateTypecast::class,
            'FakeGenerator-from-invocable-object',
        ], $generators);
    }

    public function testAddCustomGeneratorObject(): void
    {
        $conveyor = $this->createConveyor();
        $conveyor->addGenerator($conveyor::STAGE_POSTPROCESS, GenerateTypecast::class);
        $conveyor->addGenerator($conveyor::STAGE_USERLAND, RenderTables::class);
        $conveyor->addGenerator($conveyor::STAGE_RENDER, SyncTables::class);
        $conveyor->addGenerator($conveyor::STAGE_INDEX, MergeIndexes::class);

        $generators = $this->getGeneratorClassList($conveyor);

        $this->assertSame([
            ResetTables::class,
            MergeIndexes::class,
            GenerateRelations::class,
            GenerateModifiers::class,
            ValidateEntities::class,
            RenderTables::class,
            RenderRelations::class,
            RenderModifiers::class,
            ForeignKeys::class,
            SyncTables::class,
            RenderTables::class,
            GenerateTypecast::class,
            GenerateTypecast::class,
        ], $generators);
    }

    public function createConveyor(): SchemaConveyor
    {
        return new class ($this->prepareContainer()) extends SchemaConveyor {};
    }
}
