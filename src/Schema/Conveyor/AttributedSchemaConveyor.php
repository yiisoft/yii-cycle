<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use Spiral\Attributes\AttributeReader;

final class AttributedSchemaConveyor extends MetadataSchemaConveyor
{
    protected function getMetadataReader(): AttributeReader
    {
        return new AttributeReader();
    }
}
