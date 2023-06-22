<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Filter;

interface QueryBuilderFilter
{
    public function getAsWhereArguments(array $arguments, array $handlers): array;
}
