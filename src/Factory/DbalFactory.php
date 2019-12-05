<?php

namespace Yiisoft\Yii\Cycle\Factory;

use Psr\Container\ContainerInterface;
use Spiral\Database\Config\DatabaseConfig;
use Spiral\Database\DatabaseManager;
use Yiisoft\Aliases\Aliases;

class DbalFactory
{
    private $params;

    protected ContainerInterface $container;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function __invoke(ContainerInterface $container)
    {
        $this->container = $container;
        $conf = $this->prepareConfig($this->params);
        return new DatabaseManager($conf);
    }

    protected function prepareConfig($params): DatabaseConfig
    {
        if ($params instanceof DatabaseConfig) {
            return $params;
        }
        if (isset($params['connections'])) {
            // prepare connections
            foreach ($params['connections'] as &$connection) {
                $connection = $this->prepareConnection($connection);
            }
        }

        return new DatabaseConfig($params);
    }

    protected function prepareConnection(array $connection): array
    {
        // if connection option contain alias in path
        if (isset($connection['connection']) && preg_match('/^(?<proto>\w+:)?@/', $connection['connection'], $m)) {
            $proto = $m['proto'];
            $path = $this->getAlias(substr($connection['connection'], strlen($proto)));
            $connection['connection'] = $proto . $path;
        }
        return $connection;
    }

    protected function getAlias(string $alias): string
    {
        return $this->container->get(Aliases::class)->get($alias, true);
    }
}
