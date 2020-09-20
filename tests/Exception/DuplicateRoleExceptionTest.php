<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Exception;

use Yiisoft\FriendlyException\FriendlyExceptionInterface;
use Yiisoft\Yii\Cycle\Exception\DuplicateRoleException;
use PHPUnit\Framework\TestCase;

final class DuplicateRoleExceptionTest extends TestCase
{
    private const DEFAULT_ROLE = 'tag';

    private function prepareException(string $role = self::DEFAULT_ROLE): DuplicateRoleException
    {
        return new DuplicateRoleException($role);
    }

    public function testDefaultState(): void
    {
        $exception = $this->prepareException();

        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertSame(
            'The "' . self::DEFAULT_ROLE . '" role already exists in the DB schema.',
            $exception->getMessage()
        );
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
