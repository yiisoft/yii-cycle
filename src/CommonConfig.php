<?php

namespace Yiisoft\Yii\Cycle;

use Yiisoft\Yii\Cycle\Config\BaseConfig;

/**
 * Class CommonConfig
 * @package Yiisoft\Yii\Cycle\Config
 *
 * @property-read array  $entityPaths
 * @property-read string $cacheKey
 */
class CommonConfig extends BaseConfig
{
    protected $data = [
        'entityPaths' => [],
        'cacheKey'    => 'Cycle-ORM-Schema',
    ];
}
