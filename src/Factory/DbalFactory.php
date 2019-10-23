<?php

namespace Yiisoft\Yii\Cycle\Factory;

use Psr\Container\ContainerInterface;
use Spiral\Database\Config\DatabaseConfig;
use Spiral\Database\DatabaseManager;
use Spiral\Database\Driver\SQLite\SQLiteDriver;
use Yiisoft\Aliases\Aliases;

class DbalFactory
{
    private $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function __invoke(ContainerInterface $container)
    {
        $dbal = new DatabaseManager(new DatabaseConfig($this->params));
        return $dbal;
    }
}
