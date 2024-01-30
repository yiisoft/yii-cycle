<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Data\Reader\FilterHandler;

use Yiisoft\Yii\Cycle\Data\Reader\FilterHandler\GroupHandler;
use PHPUnit\Framework\TestCase;

final class GroupHandlerTest extends TestCase
{
    /**
     * @dataProvider invalidArgumentsDataProvider
     */
    public function testInvalidArgumentsException(array $arguments, string $error): void
    {
        $handler = new class () extends GroupHandler {
            public function getOperator(): string
            {
            }

            public function getAsWhereArguments(array $arguments, array $handlers): array
            {
                $this->validateArguments($arguments);
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($error);
        $handler->getAsWhereArguments($arguments, []);
    }

    public function invalidArgumentsDataProvider(): \Traversable
    {
        yield [[], 'At least one argument should be provided.'];
        yield [['foo'], 'Sub filters is not an array.'];
        yield [[['foo']], 'Sub filter is not an array.'];
        yield [[[[]]], 'At least operator should be provided.'];
        yield [[[[1]]], 'Operator is not a string.'];
        yield [[[['']]], 'The operator string cannot be empty.'];
    }
}
