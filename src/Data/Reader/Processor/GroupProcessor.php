<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Processor;

use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterHandlerInterface;

abstract class GroupProcessor implements QueryBuilderProcessor, FilterHandlerInterface
{
    protected function validateArguments(array $arguments): void
    {
        if (count($arguments) === 0) {
            throw new InvalidArgumentException('At least one argument should be provided.');
        }

        if (!is_array($arguments[0])) {
            throw new InvalidArgumentException('Sub filters is not an array.');
        }

        foreach ($arguments[0] as $subFilter) {
            if (!is_array($subFilter)) {
                throw new InvalidArgumentException('Sub filter is not an array.');
            }

            if (count($subFilter) === 0) {
                throw new InvalidArgumentException('At least operator should be provided.');
            }

            $operator = array_shift($subFilter);
            if (!is_string($operator)) {
                throw new InvalidArgumentException('Operator is not a string.');
            }

            if ($operator === '') {
                throw new InvalidArgumentException('The operator string cannot be empty.');
            }
        }
    }
}
