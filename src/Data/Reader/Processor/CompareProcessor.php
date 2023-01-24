<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Processor;

use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterHandlerInterface;

abstract class CompareProcessor implements QueryBuilderProcessor, FilterHandlerInterface
{
    protected function validateArguments(array $arguments): void
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('$arguments should contain exactly two elements.');
        }
    }

    public function getAsWhereArguments(array $arguments, array $processors): array
    {
        $this->validateArguments($arguments);
        [$field, $value] = $arguments;

        return [$field, $this->getOperator(), $value];
    }
}
