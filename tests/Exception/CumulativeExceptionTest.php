<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Exception;

use Throwable;
use Yiisoft\Yii\Cycle\Exception\CumulativeException;
use PHPUnit\Framework\TestCase;

final class CumulativeExceptionTest extends TestCase
{
    private function prepareException(Throwable ...$exceptions): CumulativeException
    {
        return new CumulativeException(...$exceptions);
    }

    public function testDefaultState(): void
    {
        $exception = $this->prepareException();

        $this->assertInstanceOf(Throwable::class, $exception);
        $this->assertSame('0 exceptions were thrown.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testGetExceptions(): void
    {
        $list = [
            new \RuntimeException(),
            new \Exception(),
            new \InvalidArgumentException(),
        ];

        $exception = $this->prepareException(...$list);

        $this->assertSame($list, $exception->getExceptions());
    }

    public function testGetMessageWithMultipleExceptions(): void
    {
        $list = [
            new \RuntimeException('Foo message.', 42),
            new \Exception('Bar message.'),
            new \InvalidArgumentException('Baz message.'),
        ];

        $exception = $this->prepareException(...$list);

        $this->assertIsInt(strpos($exception->getMessage(), '[RuntimeException] #42: Foo message.'));
        $this->assertIsInt(strpos($exception->getMessage(), '[Exception] #0: Bar message.'));
        $this->assertIsInt(strpos($exception->getMessage(), '[InvalidArgumentException] #0: Baz message.'));
    }
}
