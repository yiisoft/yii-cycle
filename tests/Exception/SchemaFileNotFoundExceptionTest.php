<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;
use Yiisoft\Yii\Cycle\Exception\SchemaFileNotFoundException;

final class SchemaFileNotFoundExceptionTest extends TestCase
{
    private const DEFAULT_FILENAME = './vendor/bin/notfound';

    private function prepareException(string $filename = self::DEFAULT_FILENAME): SchemaFileNotFoundException
    {
        return new SchemaFileNotFoundException($filename);
    }

    public function testDefaultState(): void
    {
        $exception = $this->prepareException();

        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertSame('Schema file "' . self::DEFAULT_FILENAME . '" not found.', $exception->getMessage());
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
