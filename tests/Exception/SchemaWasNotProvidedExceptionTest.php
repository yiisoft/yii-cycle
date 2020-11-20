<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;
use Yiisoft\Yii\Cycle\Exception\SchemaWasNotProvidedException;

final class SchemaWasNotProvidedExceptionTest extends TestCase
{
    private function prepareException(): SchemaWasNotProvidedException
    {
        return new SchemaWasNotProvidedException();
    }

    public function testDefaultState(): void
    {
        $exception = $this->prepareException();

        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertSame('Schema was not provided.', $exception->getMessage());
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
