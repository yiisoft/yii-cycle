<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Data\Reader\FilterHandler;

use Yiisoft\Yii\Cycle\Data\Reader\FilterHandler\CompareHandler;
use PHPUnit\Framework\TestCase;

final class CompareHandlerTest extends TestCase
{
    public function testValidateArgumentsException(): void
    {
        $handler = new class () extends CompareHandler {
            public function getOperator(): string
            {
            }

            protected function getSymbol(): string
            {
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$arguments should contain exactly two elements.');
        $handler->getAsWhereArguments(['id'], []);
    }
}
