<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Exception;

use Yiisoft\FriendlyException\FriendlyExceptionInterface;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Cycle\Exception\EmptyEntityPathsException;

final class EmptyEntityPathsExceptionTest extends TestCase
{
    private function prepareException(): EmptyEntityPathsException
    {
        return new EmptyEntityPathsException();
    }

    public function testDefaultState(): void
    {
        $exception = $this->prepareException();

        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testFriendly(): void
    {
        $exception = $this->prepareException();

        $this->assertInstanceOf(FriendlyExceptionInterface::class, $exception);
        $this->assertNotEmpty($exception->getName());
        $this->assertNotEmpty($exception->getSolution());
    }
}
