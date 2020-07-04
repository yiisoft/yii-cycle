<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Listener;

use Yiisoft\Yii\Cycle\Event\AfterMigrate;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderDispatcher;

final class MigrationListener
{
    private SchemaProviderDispatcher $schemaProviderDispatcher;

    public function __construct(SchemaProviderDispatcher $schemaProviderDispatcher)
    {
        $this->schemaProviderDispatcher = $schemaProviderDispatcher;
    }

    public function onAfterMigrate(AfterMigrate $event)
    {
        $this->schemaProviderDispatcher->clearSchema();
    }
}
