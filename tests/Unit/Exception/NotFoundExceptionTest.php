<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;
use Yiisoft\Yii\Cycle\Exception\NotFoundException;

final class NotFoundExceptionTest extends TestCase
{
    public function testDefaultState(): void
    {
        $exception = new NotFoundException(\stdClass::class);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame(
            \sprintf('No definition or class found or resolvable for "%s".', \stdClass::class),
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull( $exception->getPrevious());
    }

    public function testFriendly(): void
    {
        $exception = new NotFoundException(\stdClass::class);

        $this->assertInstanceOf(FriendlyExceptionInterface::class, $exception);
        $this->assertSame('Repository not found', $exception->getName());
        $this->assertSame(
            'Check if the class exists or if the class is properly defined.',
            $exception->getSolution()
        );
    }
}
