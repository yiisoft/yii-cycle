<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader\Processor;

interface QueryBuilderProcessor
{
    /**
     * @return \Closure|array
     */
    public function getAsWhereArguments(array $arguments, array $processors): array;
}
