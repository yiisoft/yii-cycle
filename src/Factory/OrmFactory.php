<?php

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;

final class OrmFactory
{
    private array $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function __invoke(ContainerInterface $container)
    {
        $schema = $container->get(SchemaInterface::class);
        $factory = $container->get(FactoryInterface::class);

        $orm = new ORM($factory, $schema);

        // Promise factory
        $promiseFactory = $this->params['promiseFactory'] ?? null;
        if ($promiseFactory) {
            if (!$promiseFactory instanceof PromiseFactoryInterface) {
                $promiseFactory = $container->get($promiseFactory);
            }
            $orm = $orm->withPromiseFactory($promiseFactory);
        }

        return $orm;
    }
}
