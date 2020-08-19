<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader\Processor;

final class LessThanOrEqual extends CompareProcessor
{
    public function getOperator(): string
    {
        return '<=';
    }
}
