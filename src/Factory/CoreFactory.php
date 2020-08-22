<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Spiral\Core\FactoryInterface;
use Yiisoft\Factory\Factory;

final class CoreFactory extends Factory implements FactoryInterface
{
    public function make(string $alias, array $parameters = [])
    {
        return $this->create($alias, $parameters);
    }
}
