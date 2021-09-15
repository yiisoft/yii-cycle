<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Factory\DbalFactory\Stub;

use Cycle\Database\Driver\Driver;
use Cycle\Database\Driver\SQLite\SQLiteCompiler;
use Cycle\Database\Driver\SQLite\SQLiteHandler;
use Cycle\Database\Exception\StatementException;
use Cycle\Database\Query\QueryBuilder;
use Exception;
use Psr\Log\LoggerInterface;
use Throwable;

class FakeDriver extends Driver
{
    public function __construct(array $options)
    {
        parent::__construct(
            $options,
            new SQLiteHandler(),
            new SQLiteCompiler('""'),
            QueryBuilder::defaultBuilder()
        );
    }

    public function getLogger(): ?LoggerInterface
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
