<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;

abstract class BaseBadDeclaration extends TestCase
{
    private const RECEIVED_PATTERN = '/%s was received instead\\./';

    abstract protected function prepareException($argument): BadDeclarationException;

    public static function ArgumentValueProvider(): array
    {
        return [
            [null, 'Null'],
            [42, 'Int'],
            [new \DateTimeImmutable(), 'Instance of DateTimeImmutable'],
            [STDIN, 'Resource \\(stream\\)'],
            [[], 'Array'],
        ];
    }

    /**
     * @dataProvider ArgumentValueProvider
     */
    public function testTypeMessage($value, string $message): void
    {
        $exception = $this->prepareException($value);
        $pattern = sprintf(self::RECEIVED_PATTERN, $message);

        $this->assertMatchesRegularExpression($pattern, $exception->getMessage());
    }
}
