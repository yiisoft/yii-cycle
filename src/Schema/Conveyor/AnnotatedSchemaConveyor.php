<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use Spiral\Attributes\AnnotationReader;

/**
 * @deprecated Use {@see AttributedSchemaConveyor} instead.
 *
 * @psalm-suppress DeprecatedClass
 */
final class AnnotatedSchemaConveyor extends MetadataSchemaConveyor
{
    protected function getMetadataReader(): AnnotationReader
    {
        return new AnnotationReader();
    }
}
