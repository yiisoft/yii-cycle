<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Processor;

use Spiral\Database\Injection\Parameter;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

final class In implements QueryBuilderProcessor, FilterProcessorInterface
{
    public function getOperator(): string
    {
        return 'in';
    }

    public function getAsWhereArguments(array $arguments, array $processors): array
    {
        if (count($arguments) !== 2) {
            throw new \InvalidArgumentException('$arguments should contain exactly two elements.');
        }

        [$field, $value] = $arguments;

        return [$field, $this->getOperator(), new Parameter($value)];
    }
}
