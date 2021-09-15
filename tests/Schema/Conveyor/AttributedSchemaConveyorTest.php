<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Conveyor;

use Yiisoft\Yii\Cycle\Schema\Conveyor\AttributedSchemaConveyor;

final class AttributedSchemaConveyorTest extends MetadataSchemaConveyorTest
{
    public function createConveyor(array $entityPaths = ['@test-dir']): AttributedSchemaConveyor
    {
        $conveyor = new AttributedSchemaConveyor($this->prepareContainer());
        $conveyor->addEntityPaths($entityPaths);

        return $conveyor;
    }
}
