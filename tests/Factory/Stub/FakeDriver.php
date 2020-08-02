<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Factory\Stub;

use Exception;
use Spiral\Database\Driver\Driver;
use Spiral\Database\Driver\SQLite\SQLiteCompiler;
use Spiral\Database\Driver\SQLite\SQLiteHandler;
use Spiral\Database\Exception\StatementException;
use Spiral\Database\Query\QueryBuilder;
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

    public function getLogger()
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
