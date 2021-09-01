<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Conveyor;

use Yiisoft\Yii\Cycle\Schema\Conveyor\CompositedSchemaConveyor;

final class CompositedSchemaConveyorTest extends MetadataSchemaConveyorTest
{
    public function createConveyor($entityPaths = ['@test-dir']): CompositedSchemaConveyor
    {
        $conveyor = new CompositedSchemaConveyor($this->prepareContainer());
        $conveyor->addEntityPaths($entityPaths);

        return $conveyor;
    }
}
