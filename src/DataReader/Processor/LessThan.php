<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader\Processor;

final class LessThan extends CompareProcessor
{
    public function getOperator(): string
    {
        return '<';
    }
}
