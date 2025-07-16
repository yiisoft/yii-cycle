<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\DatabaseManager;
use Cycle\Database\Driver\Driver;

final class DbalFactory
{
    public function __construct(
        private readonly array|DatabaseConfig $dbalConfig,
        private readonly mixed $logger = null
    ) {
    }

    public function create(): DatabaseManager
    {
        $dbal = new DatabaseManager(
            $this->prepareConfig($this->dbalConfig)
        );

        if ($this->logger !== null) {
            $logger = $this->logger;
            $dbal->setLogger($logger);
            /** Remove when issue is resolved {@link https://github.com/cycle/orm/issues/60} */
            $drivers = $dbal->getDrivers();
            array_walk($drivers, static fn (Driver $driver) => $driver->setLogger($logger));
        }

        return $dbal;
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
