<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Processor;

final class LessThan extends CompareProcessor
{
    public function getOperator(): string
    {
        return '<';
    }
}
