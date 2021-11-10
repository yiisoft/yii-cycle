<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\Database\Driver\Driver;
use Exception;
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\DatabaseManager;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Spiral\Core\FactoryInterface;

final class DbalFactory
{
    private array|DatabaseConfig $dbalConfig;

    /** @var LoggerInterface|string|null */
    private mixed $logger = null;

    public function __construct(array|DatabaseConfig $config)
    {
        if (is_array($config) && array_key_exists('query-logger', $config)) {
            $this->logger = $config['query-logger'];
            unset($config['query-logger']);
        }
        $this->dbalConfig = $config;
    }

    public function __invoke(ContainerInterface $container): DatabaseManager
    {
        $dbal = new DatabaseManager(
            $this->prepareConfig($this->dbalConfig),
            $container->get(FactoryInterface::class)
        );

        if ($this->logger !== null) {
            $logger = $this->prepareLogger($container, $this->logger);
            $dbal->setLogger($logger);
            /** Remove when issue is resolved {@link https://github.com/cycle/orm/issues/60} */
            $drivers = $dbal->getDrivers();
            array_walk($drivers, static fn (Driver $driver) => $driver->setLogger($logger));
        }

        return $dbal;
    }

    /**
     * @param LoggerInterface|string $logger
     *
     * @throws Exception
     *
     * @return LoggerInterface
     */
    private function prepareLogger(ContainerInterface $container, mixed $logger): LoggerInterface
    {
        if (is_string($logger)) {
            $logger = $container->get($logger);
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
     *
     * @return DatabaseConfig
     */
    private function prepareConfig(array|DatabaseConfig $config): DatabaseConfig
    {
        if ($config instanceof DatabaseConfig) {
            return $config;
        }

        return new DatabaseConfig($config);
    }
}
