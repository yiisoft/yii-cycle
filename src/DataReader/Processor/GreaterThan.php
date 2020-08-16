<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader\Processor;

final class GreaterThan extends CompareProcessor
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\GreaterThan::getOperator();
    }
}
