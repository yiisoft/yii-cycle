<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Spiral\Core\FactoryInterface;
use Spiral\Database\Config\DatabaseConfig;
use Spiral\Database\DatabaseManager;
use Spiral\Database\Driver\Driver;
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

        $dbal = new DatabaseManager(
            $this->prepareConfig($this->dbalConfig),
            $this->container->get(FactoryInterface::class)
        );

        if ($this->logger !== null) {
            $logger = $this->prepareLogger($this->logger);
            $dbal->setLogger($logger);
            /** Remove when issue is resolved {@link https://github.com/cycle/orm/issues/60} */
            $drivers = $dbal->getDrivers();
            array_walk($drivers, static fn (Driver $driver) => $driver->setLogger($logger));
        }

        return $dbal;
    }

    /**
     * @param string|LoggerInterface $logger
     * @return LoggerInterface
     * @throws Exception
     */
    private function prepareLogger($logger): LoggerInterface
    {
        if (is_string($logger)) {
            /** @psalm-suppress PossiblyNullReference */
            $logger = $this->container->get($logger);
        }
        if (!$logger instanceof LoggerInterface) {
            throw new RuntimeException(
                sprintf('Logger definition should be subclass of %s.', LoggerInterface::class)
            );
        }
        return $logger;
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
        /** @psalm-suppress PossiblyNullReference */
        return $this->container->get(Aliases::class)->get($alias);
    }
}
