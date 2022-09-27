<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Listener;

use Yiisoft\Yii\Cycle\Event\AfterMigrate;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class MigrationListener
{
    public function __construct(private SchemaProviderInterface $schemaProvider)
    {
    }

    public function onAfterMigrate(AfterMigrate $event): void
    {
        $this->schemaProvider->clear();
    }
}
