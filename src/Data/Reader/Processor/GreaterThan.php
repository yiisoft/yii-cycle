<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Processor;

final class GreaterThan extends CompareProcessor
{
    public function getOperator(): string
    {
        return '>';
    }
}
