<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Stub;

use Cycle\Database\Config\DriverConfig;
use Cycle\Database\Driver\Driver;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Driver\SQLite\SQLiteCompiler;
use Cycle\Database\Driver\SQLite\SQLiteHandler;
use Cycle\Database\Exception\StatementException;
use Cycle\Database\Query\QueryBuilder;
use Exception;
use Psr\Log\LoggerInterface;
use Throwable;

class FakeDriver extends Driver
{
    private function __construct(DriverConfig $config)
    {
        parent::__construct(
            $config,
            new SQLiteHandler(),
            new SQLiteCompiler('""'),
            QueryBuilder::defaultBuilder()
        );
    }

    public static function create(DriverConfig $config): DriverInterface
    {
        return new self($config);
    }

    final public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    protected function mapException(Throwable $exception, string $query): StatementException
    {
        return new StatementException(new Exception(), 'fake query');
    }

    public function getType(): string
    {
        return 'fake';
    }
}
