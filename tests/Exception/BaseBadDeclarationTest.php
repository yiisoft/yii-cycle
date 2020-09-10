<?php

namespace Yiisoft\Yii\Cycle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;

abstract class BaseBadDeclarationTest extends TestCase
{
    private const RECEIVED_PATTERN = '/%s was received instead\\./';

    abstract protected function prepareException($argument): BadDeclarationException;

    public function ArgumentValueProvider(): array
    {
        return [
            [null, 'NULL'],
            [42, 'Integer'],
            [new \DateTimeImmutable(), 'Instance of DateTimeImmutable'],
            [STDIN, 'Resource'],
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
