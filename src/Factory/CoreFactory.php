<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Spiral\Core\FactoryInterface as SpiralFactoryInterface;
use Yiisoft\Factory\Factory;

final class CoreFactory extends Factory implements SpiralFactoryInterface
{

    public function make(string $alias, array $parameters = [])
    {
        return $this->create($alias, $parameters);
    }
}
