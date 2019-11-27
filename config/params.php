<?php

use Yiisoft\Yii\Cycle\Command;
use Yiisoft\Yii\Cycle;

return [
    // Console commands
    'commands' => [
        'migrate/create' => Command\CreateCommand::class,
        'migrate/generate' => Command\GenerateCommand::class,
        'migrate/up' => Command\UpCommand::class,
        'migrate/down' => Command\DownCommand::class,
        'migrate/list' => Command\ListCommand::class,
    ],
];
