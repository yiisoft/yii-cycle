<?php

namespace Yiisoft\Yii\Cycle;

use Yiisoft\Yii\Cycle\Config\BaseConfig;

/**
 * @property-read array  $entityPaths
 * @property-read string $cacheKey
 */
class CycleCommonConfig extends BaseConfig
{
    protected $entityPaths = [];
    protected $cacheKey = 'Cycle-ORM-Schema';
}
