<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Attributes\AnnotationReader;
use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\Composite\SelectiveReader;
use Spiral\Attributes\ReaderInterface;

final class CompositedSchemaConveyor extends MetadataSchemaConveyor
{
    protected function getMetadataReader(): ?ReaderInterface
    {
        return new SelectiveReader([
            new AttributeReader(),
            new AnnotationReader(),
        ]);
    }
}
