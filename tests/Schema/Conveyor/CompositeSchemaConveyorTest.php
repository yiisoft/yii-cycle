<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Conveyor;

use Yiisoft\Yii\Cycle\Schema\Conveyor\CompositeSchemaConveyor;

final class CompositeSchemaConveyorTest extends MetadataSchemaConveyorTest
{
    public function createConveyor(array $entityPaths = ['@test-dir']): CompositeSchemaConveyor
    {
        $conveyor = new CompositeSchemaConveyor($this->prepareContainer());
        $conveyor->addEntityPaths($entityPaths);

        return $conveyor;
    }
}
