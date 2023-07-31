<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader;

interface QueryBuilderFilterHandler
{
    public function getAsWhereArguments(array $arguments, array $handlers): array;
}
