<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Filter;

final class Equals extends Compare
{
    public function getOperator(): string
    {
        return '=';
    }
}
