<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use Spiral\Attributes\AnnotationReader;

final class AnnotatedSchemaConveyor extends MetadataSchemaConveyor
{
    private bool $isAutoloadRegistered = false;

    public function getGenerators(): array
    {
        if (!$this->isAutoloadRegistered) {
            /**
             * autoload annotations
             *
             * @psalm-suppress DeprecatedMethod
             */
            $this->isAutoloadRegistered = true;
        }

        return parent::getGenerators();
    }

    protected function getMetadataReader(): AnnotationReader
    {
        return new AnnotationReader();
    }
}
