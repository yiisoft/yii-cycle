<?php

namespace Yiisoft\Yii\Cycle\Tests\Conveyor;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Cycle\Conveyor\AnnotatedSchemaConveyor;
use Yiisoft\Yii\Cycle\Exception\EmptyEntityPathsException;
use Yiisoft\Yii\Cycle\Tests\Conveyor\Stub\FakeContainer;
use Yiisoft\Yii\Cycle\Tests\Conveyor\Stub\FakeGenerator;

class AnnotatedSchemaConveyorTest extends TestCase
{
    public function testDefaultGeneratorsOrder(): void
    {
        $conveyor = $this->createConveyor();

        // get generators list
        /** @var string[] $generators */
        $generators = array_map(
            fn ($value) => $value instanceof FakeGenerator ? $value->originClass() : get_class($value),
            $conveyor->getGenerators()
        );

        $this->assertSame([
            'Cycle\Schema\Generator\ResetTables',
            'Cycle\Annotated\Embeddings',
            'Cycle\Annotated\Entities',
            'Cycle\Annotated\MergeColumns',
            'Cycle\Schema\Generator\GenerateRelations',
            'Cycle\Schema\Generator\ValidateEntities',
            'Cycle\Schema\Generator\RenderTables',
            'Cycle\Schema\Generator\RenderRelations',
            'Cycle\Annotated\MergeIndexes',
            'Cycle\Schema\Generator\GenerateTypecast',
        ], $generators);
    }

    public function testAddCustomGenerator(): void
    {
        $conveyor = $this->createConveyor();
        $conveyor->addGenerator($conveyor::STAGE_USERLAND, \Cycle\Schema\Generator\SyncTables::class);

        // get generators list
        /** @var string[] $generators */
        $generators = array_map(
            fn ($value) => $value instanceof FakeGenerator ? $value->originClass() : get_class($value),
            $conveyor->getGenerators()
        );

        $this->assertSame([
            'Cycle\Schema\Generator\ResetTables',
            'Cycle\Annotated\Embeddings',
            'Cycle\Annotated\Entities',
            'Cycle\Annotated\MergeColumns',
            'Cycle\Schema\Generator\GenerateRelations',
            'Cycle\Schema\Generator\ValidateEntities',
            'Cycle\Schema\Generator\RenderTables',
            'Cycle\Schema\Generator\RenderRelations',
            'Cycle\Annotated\MergeIndexes',
            'Cycle\Schema\Generator\SyncTables',
            'Cycle\Schema\Generator\GenerateTypecast',
        ], $generators);
    }

    public function testEmptyEntityPaths(): void
    {
        $conveyor = $this->createConveyor([]);

        $this->expectException(EmptyEntityPathsException::class);

        $conveyor->getGenerators();
    }

    public function createConveyor($entityPaths = ['@test-dir']): AnnotatedSchemaConveyor
    {
        $conveyor = new AnnotatedSchemaConveyor(new FakeContainer($this));
        $conveyor->addEntityPaths($entityPaths);

        return $conveyor;
    }
}
