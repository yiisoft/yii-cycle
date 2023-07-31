<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\FilterHandler;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Yii\Cycle\Data\Reader\QueryBuilderFilterHandler;

final class LikeHandler implements QueryBuilderFilterHandler, FilterHandlerInterface
{
    public function getOperator(): string
    {
        return Like::getOperator();
    }

    public function getAsWhereArguments(array $arguments, array $handlers): array
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('$arguments should contain exactly two elements.');
        }

        [$field, $value] = $arguments;

        return [$field, 'like', $value];
    }
}
