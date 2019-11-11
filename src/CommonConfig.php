<?php

namespace Yiisoft\Yii\Cycle;

use Yiisoft\Yii\Cycle\Config\BaseConfig;

/**
 * @property array  $entityPaths
 * @property string $cacheKey
 */
class CommonConfig extends BaseConfig
{
    protected $data = [
        'entityPaths' => [],
        'cacheKey'    => 'Cycle-ORM-Schema',
    ];
}
