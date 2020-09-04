<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Conveyor;

use Yiisoft\Yii\Cycle\Exception\EmptyEntityPathsException;
use Yiisoft\Yii\Cycle\Schema\Conveyor\AnnotatedSchemaConveyor;
use Yiisoft\Yii\Cycle\Tests\Schema\Conveyor\Stub\FakeGenerator;

class AnnotatedSchemaConveyorTest extends BaseConveyorTest
{
    public function testDefaultGeneratorsOrder(): void
    {
        $conveyor = $this->createConveyor();

        $generators = $this->getGeneratorClassList($conveyor);

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

        $generators = $this->getGeneratorClassList($conveyor);

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

    public function testAnnotatedGeneratorsAddedOnlyOnce(): void
    {
        $conveyor = $this->createConveyor();

        $conveyor->getGenerators();
        $conveyor->getGenerators();
        $generators = $this->getGeneratorClassList($conveyor);

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

    public function createConveyor($entityPaths = ['@test-dir']): AnnotatedSchemaConveyor
    {
        $conveyor = new AnnotatedSchemaConveyor($this->prepareContainer());
        $conveyor->addEntityPaths($entityPaths);

        return $conveyor;
    }
}
