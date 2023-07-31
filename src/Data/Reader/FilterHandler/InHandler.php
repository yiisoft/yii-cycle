<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\FilterHandler;

use Cycle\Database\Injection\Parameter;
use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Yii\Cycle\Data\Reader\QueryBuilderFilterHandler;

final class InHandler implements QueryBuilderFilterHandler, FilterHandlerInterface
{
    public function getOperator(): string
    {
        return In::getOperator();
    }

    public function getAsWhereArguments(array $arguments, array $handlers): array
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('$arguments should contain exactly two elements.');
        }

        [$field, $value] = $arguments;

        return [$field, 'in', new Parameter($value)];
    }
}
