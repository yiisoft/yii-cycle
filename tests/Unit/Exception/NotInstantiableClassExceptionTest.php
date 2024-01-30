<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Exception;

use Cycle\ORM\RepositoryInterface;
use PHPUnit\Framework\TestCase;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;
use Yiisoft\Yii\Cycle\Exception\NotInstantiableClassException;

final class NotInstantiableClassExceptionTest extends TestCase
{
    public function testDefaultState(): void
    {
        $exception = new NotInstantiableClassException(\stdClass::class);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame(\sprintf(
            'Can not instantiate "%s" because it is not a subclass of "%s".',
            \stdClass::class,
            RepositoryInterface::class
        ), $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testFriendly(): void
    {
        $exception = new NotInstantiableClassException(\stdClass::class);

        $this->assertInstanceOf(FriendlyExceptionInterface::class, $exception);
        $this->assertSame('Repository not instantiable', $exception->getName());
        $this->assertSame(
            'Make sure that the class is instantiable and implements Cycle\ORM\RepositoryInterface',
            $exception->getSolution()
        );
    }
}
