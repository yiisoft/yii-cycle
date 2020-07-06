<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Listener;

use Yiisoft\Yii\Cycle\Event\AfterMigrate;
use Yiisoft\Yii\Cycle\Schema\SchemaManager;

final class MigrationListener
{
    private SchemaManager $schemaManager;

    public function __construct(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    public function onAfterMigrate(AfterMigrate $event)
    {
        $this->schemaManager->clear();
    }
}
