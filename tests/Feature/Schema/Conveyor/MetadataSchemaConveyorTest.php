<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Conveyor;

use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Annotated\TableInheritance;
use Cycle\Schema\Generator\GenerateModifiers;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\RenderModifiers;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Generator\ValidateEntities;
use Yiisoft\Yii\Cycle\Exception\EmptyEntityPathsException;
use Yiisoft\Yii\Cycle\Schema\Conveyor\CompositeSchemaConveyor;
use Yiisoft\Yii\Cycle\Schema\Conveyor\MetadataSchemaConveyor;

class MetadataSchemaConveyorTest extends BaseConveyor
{
    final public function testGetTableNamingDefault(): void
    {
        $conveyor = $this->createConveyor();

        $this->assertSame(Entities::TABLE_NAMING_SINGULAR, $conveyor->getTableNaming());
    }

    final public static function tableNamingProvider(): array
    {
        return [
            [Entities::TABLE_NAMING_PLURAL],
            [Entities::TABLE_NAMING_SINGULAR],
            [Entities::TABLE_NAMING_NONE],
        ];
    }

    /**
     * @dataProvider tableNamingProvider
     */
    final public function testSetTableNaming(int $naming): void
    {
        $conveyor = $this->createConveyor();

        $conveyor->setTableNaming($naming);

        $this->assertSame($naming, $conveyor->getTableNaming());
    }

    final public function testDefaultGeneratorsOrder(): void
    {
        $conveyor = $this->createConveyor();

        $generators = $this->getGeneratorClassList($conveyor);

        $this->assertSame([
            ResetTables::class,
            Embeddings::class,
            Entities::class,
            TableInheritance::class,
            MergeColumns::class,
            GenerateRelations::class,
            GenerateModifiers::class,
            ValidateEntities::class,
            RenderTables::class,
            RenderRelations::class,
            RenderModifiers::class,
            MergeIndexes::class,
            GenerateTypecast::class,
        ], $generators);
    }

    final public function testAddCustomGenerator(): void
    {
        $conveyor = $this->createConveyor();
        $conveyor->addGenerator($conveyor::STAGE_USERLAND, \Cycle\Schema\Generator\SyncTables::class);

        $generators = $this->getGeneratorClassList($conveyor);

        $this->assertSame([
            ResetTables::class,
            Embeddings::class,
            Entities::class,
            TableInheritance::class,
            MergeColumns::class,
            GenerateRelations::class,
            GenerateModifiers::class,
            ValidateEntities::class,
            RenderTables::class,
            RenderRelations::class,
            RenderModifiers::class,
            MergeIndexes::class,
            SyncTables::class,
            GenerateTypecast::class,
        ], $generators);
    }

    final public function testEmptyEntityPaths(): void
    {
        $conveyor = $this->createConveyor([]);

        $this->expectException(EmptyEntityPathsException::class);

        $conveyor->getGenerators();
    }

    final public function testAnnotatedGeneratorsAddedOnlyOnce(): void
    {
        $conveyor = $this->createConveyor();

        $conveyor->getGenerators();
        $conveyor->getGenerators();
        $generators = $this->getGeneratorClassList($conveyor);

        $this->assertSame([
            ResetTables::class,
            Embeddings::class,
            Entities::class,
            TableInheritance::class,
            MergeColumns::class,
            GenerateRelations::class,
            GenerateModifiers::class,
            ValidateEntities::class,
            RenderTables::class,
            RenderRelations::class,
            RenderModifiers::class,
            MergeIndexes::class,
            GenerateTypecast::class,
        ], $generators);
    }

    /**
     * @param string[] $entityPaths
     */
    public function createConveyor(array $entityPaths = ['@test-dir']): MetadataSchemaConveyor
    {
        $conveyor = new CompositeSchemaConveyor($this->prepareContainer());
        $conveyor->addEntityPaths($entityPaths);

        return $conveyor;
    }
}
