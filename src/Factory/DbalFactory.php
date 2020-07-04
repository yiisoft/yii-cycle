<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Spiral\Database\Config\DatabaseConfig;
use Spiral\Database\DatabaseManager;
use Yiisoft\Aliases\Aliases;

final class DbalFactory
{
    /** @var array|DatabaseConfig */
    private $dbalConfig;
    /** @var null|string|LoggerInterface */
    private $logger = null;
    private ?ContainerInterface $container = null;

    /**
     * @param array|DatabaseConfig $config
     */
    public function __construct($config)
    {
        if (is_array($config) && array_key_exists('query-logger', $config)) {
            $this->logger = $config['query-logger'];
            unset($config['query-logger']);
        }
        $this->dbalConfig = $config;
    }

    public function __invoke(ContainerInterface $container)
    {
        $this->container = $container;
        $conf = $this->prepareConfig($this->dbalConfig);
        $dbal = new DatabaseManager($conf);

        if ($this->logger !== null) {
            if (!$this->logger instanceof LoggerInterface) {
                $this->logger = $container->get($this->logger);
            }
            $dbal->setLogger($this->logger);
            /** Remove when issue is resolved @link https://github.com/cycle/orm/issues/60 */
            foreach ($dbal->getDrivers() as $driver) {
                $driver->setLogger($this->logger);
            }
        }

        return $dbal;
    }

    /**
     * @param array|DatabaseConfig $config
     * @return DatabaseConfig
     */
    private function prepareConfig($config): DatabaseConfig
    {
        if ($config instanceof DatabaseConfig) {
            return $config;
        }
        if (isset($config['connections'])) {
            // prepare connections
            foreach ($config['connections'] as &$connection) {
                $connection = $this->prepareConnection($connection);
            }
        }

        return new DatabaseConfig($config);
    }

    private function prepareConnection(array $connection): array
    {
        // if connection option contain alias in path
        if (isset($connection['connection']) && preg_match('/^(?<proto>\w+:)?@/', $connection['connection'], $m)) {
            $proto = $m['proto'];
            $path = $this->getAlias(substr($connection['connection'], strlen($proto)));
            $connection['connection'] = $proto . $path;
        }
        return $connection;
    }

    private function getAlias(string $alias): string
    {
        return $this->container->get(Aliases::class)->get($alias);
    }
}
