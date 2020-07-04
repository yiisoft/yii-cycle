<?php

use Yiisoft\Arrays\Modifier\ReverseBlockMerge;
use Yiisoft\Yii\Cycle\Event\AfterMigrate;
use Yiisoft\Yii\Cycle\Listener\MigrationListener;

return [
    // ReverseBlockMerge::class => new ReverseBlockMerge(),
    AfterMigrate::class => [
        [MigrationListener::class, 'onAfterMigrate']
    ],
];
