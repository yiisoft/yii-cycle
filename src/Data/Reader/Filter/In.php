<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Filter;

use Cycle\Database\Injection\Parameter;
use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterHandlerInterface;

final class In implements QueryBuilderFilter, FilterHandlerInterface
{
    public function getOperator(): string
    {
        return 'in';
    }

    public function getAsWhereArguments(array $arguments, array $handlers): array
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('$arguments should contain exactly two elements.');
        }

        [$field, $value] = $arguments;

        return [$field, $this->getOperator(), new Parameter($value)];
    }
}
