<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Psr\Container\ContainerInterface;
use Spiral\Core\FactoryInterface;
use Yiisoft\Injector\Injector;

final class CycleDynamicFactory implements FactoryInterface
{
    private Injector $injector;
    public function __construct(ContainerInterface $container)
    {
        $this->injector = new Injector($container);
    }

    public function make(string $alias, array $parameters = [])
    {
        return $this->injector->make($alias, $parameters);
    }
}
