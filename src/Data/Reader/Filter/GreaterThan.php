<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Filter;

final class GreaterThan extends Compare
{
    public function getOperator(): string
    {
        return '>';
    }
}
