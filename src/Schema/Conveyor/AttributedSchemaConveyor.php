<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use Spiral\Attributes\AnnotationReader;

final class AttributedSchemaConveyor extends CompositedSchemaConveyor
{
    protected function getMetadataReader(): AnnotationReader
    {
        return new AnnotationReader();
    }
}
