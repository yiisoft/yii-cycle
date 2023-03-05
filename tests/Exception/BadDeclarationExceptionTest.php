<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Exception;

use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;

final class BadDeclarationExceptionTest extends BaseBadDeclaration
{
    private const DEFAULT_CLASS = \stdClass::class;
    private const DEFAULT_PARAMETER = 'Default parameter';
    private const DEFAULT_MESSAGE_PATTERN = '/Default parameter should be instance of stdClass or its declaration\\./';

    protected function prepareException(
        $argument,
        string $parameter = self::DEFAULT_PARAMETER,
        string $class = self::DEFAULT_CLASS
    ): BadDeclarationException {
        return new BadDeclarationException($parameter, $class, $argument);
    }

    public function testDefaultState(): void
    {
        $exception = $this->prepareException(null);

        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertSame(0, $exception->getCode());
        $this->assertMatchesRegularExpression(self::DEFAULT_MESSAGE_PATTERN, $exception->getMessage());
    }
}
