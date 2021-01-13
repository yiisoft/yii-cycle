<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Processor;

interface QueryBuilderProcessor
{
    public function getAsWhereArguments(array $arguments, array $processors): array;
}
