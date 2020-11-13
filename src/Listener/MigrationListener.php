<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Listener;

use Yiisoft\Yii\Cycle\Event\AfterMigrate;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class MigrationListener
{
    private SchemaProviderInterface $schemaProvider;

    public function __construct(SchemaProviderInterface $schemaProvider)
    {
        $this->schemaProvider = $schemaProvider;
    }

    public function onAfterMigrate(AfterMigrate $event): void
    {
        $this->schemaProvider->clear();
    }
}
