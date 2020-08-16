<?php

namespace Yiisoft\Yii\Cycle\DataReader\Processor;

use Cycle\ORM\Select\QueryBuilder;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

abstract class CompareProcessor implements QueryBuilderProcessor, FilterProcessorInterface
{
    protected function validateArguments(array $arguments): void
    {
        if (count($arguments) !== 2) {
            throw new \InvalidArgumentException('$arguments should contain exactly two elements.');
        }
    }
    public function getAsWhereArguments(array $arguments, array $processors): array
    {
        $this->validateArguments($arguments);
        [$field, $value] = $arguments;

        return [$field, $this->getOperator(), $value];
    }
}
