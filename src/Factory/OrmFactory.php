<?php

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\Schema;
use Psr\Container\ContainerInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;

final class OrmFactory
{
    private array $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function __invoke(ContainerInterface $container)
    {
        $dbal = $container->get(DatabaseManager::class);

        $schema = new Schema(
            $container->get(CycleOrmHelper::class)->getCurrentSchemaArray(true, $this->params['generators'] ?? [])
        );

        $orm = (new ORM(new Factory($dbal)))->withSchema($schema);
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
