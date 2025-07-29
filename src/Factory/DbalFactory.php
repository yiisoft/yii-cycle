<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final class DbalFactory
{
    public function __construct(
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function create(array $dbalConfig): DatabaseManager
    {
        $loggingEnabled = $dbalConfig['query-logging'] ?? false;
        $dbal = new DatabaseManager(
            new DatabaseConfig($dbalConfig)
        );

        if ($this->logger !== null && $loggingEnabled === true) {
            $logger = $this->logger;
            $dbal->setLogger($logger);
        }

        return $dbal;
    }

}
