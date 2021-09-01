<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Conveyor;

use Yiisoft\Yii\Cycle\Schema\Conveyor\AnnotatedSchemaConveyor;

final class AnnotatedSchemaConveyorTest extends MetadataSchemaConveyorTest
{
    public function createConveyor($entityPaths = ['@test-dir']): AnnotatedSchemaConveyor
    {
        $conveyor = new AnnotatedSchemaConveyor($this->prepareContainer());
        $conveyor->addEntityPaths($entityPaths);

        return $conveyor;
    }
}
