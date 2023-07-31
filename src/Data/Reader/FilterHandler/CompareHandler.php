<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\FilterHandler;

use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Yii\Cycle\Data\Reader\QueryBuilderFilterHandler;

abstract class CompareHandler implements QueryBuilderFilterHandler, FilterHandlerInterface
{
    abstract protected function getSymbol(): string;

    protected function validateArguments(array $arguments): void
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('$arguments should contain exactly two elements.');
        }
    }

    public function getAsWhereArguments(array $arguments, array $handlers): array
    {
        $this->validateArguments($arguments);
        [$field, $value] = $arguments;

        return [$field, $this->getSymbol(), $value];
    }
}
