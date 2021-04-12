<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Spiral\Attributes\AnnotationReader;

final class AnnotatedSchemaConveyor extends CompositedSchemaConveyor
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
            AnnotationRegistry::registerLoader('class_exists');
            $this->isAutoloadRegistered = true;
        }

        return parent::getGenerators();
    }

    protected function getMetadataReader(): AnnotationReader
    {
        return new AnnotationReader();
    }
}
