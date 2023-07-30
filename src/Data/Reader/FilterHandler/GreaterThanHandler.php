<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\FilterHandler;

use Yiisoft\Data\Reader\Filter\GreaterThan;

final class GreaterThanHandler extends CompareHandler
{
    public function getOperator(): string
    {
        return GreaterThan::getOperator();
    }

    protected function getSymbol(): string
    {
        return '>';
    }
}
