<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader\Processor;

use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

final class Like implements QueryBuilderProcessor, FilterProcessorInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Like::getOperator();
    }
    public function getAsWhereArguments(array $arguments, array $processors): array
    {
        if (count($arguments) !== 2) {
            throw new \InvalidArgumentException('$arguments should contain exactly two elements.');
        }

        [$field, $value] = $arguments;

        return [$field, $this->getOperator(), $value];
    }
}
