<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Psr\Container\ContainerInterface;
use Spiral\Core\FactoryInterface as SpiralFactoryInterface;
use Yiisoft\Factory\Factory;

final class CoreFactory implements SpiralFactoryInterface
{

    private $factory;

    public function __construct(ContainerInterface $container)
    {
        $this->factory = new Factory($container);
    }

    public function make(string $alias, array $parameters = [])
    {
        return $this->factory->create($alias, $parameters);
    }
}
