<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader\Processor;

final class Equals extends CompareProcessor
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Equals::getOperator();
    }
}
