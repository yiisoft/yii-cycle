<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Processor;

final class Equals extends CompareProcessor
{
    public function getOperator(): string
    {
        return '=';
    }
}
