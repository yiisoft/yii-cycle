<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use Spiral\Attributes\AnnotationReader;

final class AnnotatedSchemaConveyor extends MetadataSchemaConveyor
{
    protected function getMetadataReader(): AnnotationReader
    {
        return new AnnotationReader();
    }
}
