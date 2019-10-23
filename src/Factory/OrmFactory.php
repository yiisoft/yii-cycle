<?php

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\Schema;
use Psr\Container\ContainerInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;

class OrmFactory
{
    /** @var ContainerInterface */
    private $container;

    public function __invoke(ContainerInterface $container)
    {
        $this->container = $container;
        $dbal = $container->get(DatabaseManager::class);

        $schema = new Schema($this->container->get(CycleOrmHelper::class)->getCurrentSchemaArray());

        return (new ORM(new Factory($dbal)))->withSchema($schema);
    }
}
