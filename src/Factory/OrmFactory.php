<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;

final class OrmFactory
{
    /** @var null|PromiseFactoryInterface|string  */
    private $promiseFactory = null;

    /**
     * OrmFactory constructor.
     * @param null|PromiseFactoryInterface|string $promiseFactory
     */
    public function __construct($promiseFactory = null)
    {
        $this->promiseFactory = $promiseFactory;
    }

    public function __invoke(ContainerInterface $container)
    {
        $schema = $container->get(SchemaInterface::class);
        $factory = $container->get(FactoryInterface::class);

        $orm = new ORM($factory, $schema);

        return $this->addPromiseFactory($orm, $container);
    }

    private function addPromiseFactory(ORM $orm, ContainerInterface $container): ORM
    {
        if ($this->promiseFactory === null) {
            return $orm;
        }
        $promiseFactory = is_string($this->promiseFactory)
            ? $container->get($this->promiseFactory)
            : $this->promiseFactory;


        if (!$promiseFactory instanceof PromiseFactoryInterface) {
            throw new BadDeclarationException('Promise factory', PromiseFactoryInterface::class, $promiseFactory);
        }
        return $orm->withPromiseFactory($promiseFactory);
    }
}
