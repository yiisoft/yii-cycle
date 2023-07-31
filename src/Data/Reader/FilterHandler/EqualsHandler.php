<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\FilterHandler;

use Yiisoft\Data\Reader\Filter\Equals;

final class EqualsHandler extends CompareHandler
{
    public function getOperator(): string
    {
        return Equals::getOperator();
    }

    protected function getSymbol(): string
    {
        return '=';
    }
}
