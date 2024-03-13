<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Listener;

use Cycle\Schema\Provider\SchemaProviderInterface;
use Yiisoft\Yii\Cycle\Event\AfterMigrate;

final class MigrationListener
{
    public function __construct(
        private readonly SchemaProviderInterface $schemaProvider,
    ) {
    }

    public function onAfterMigrate(AfterMigrate $event): void
    {
        $this->schemaProvider->clear();
    }
}
