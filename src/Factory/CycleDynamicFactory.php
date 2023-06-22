<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Spiral\Core\FactoryInterface;
use Yiisoft\Injector\Injector;

final class CycleDynamicFactory implements FactoryInterface
{
    private Injector $injector;

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    public function make(string $alias, array $parameters = []): mixed
    {
        /** @psalm-var class-string $alias */
        return $this->injector->make($alias, $parameters);
    }
}
