<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader\Processor;

use Cycle\ORM\Select\QueryBuilder;

final class All extends GroupProcessor
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\All::getOperator();
    }
    public function getAsWhereArguments(array $arguments, array $processors): array
    {
        $this->validateArguments($arguments);
        return [
            static function (QueryBuilder $select) use ($arguments, $processors) {
                foreach ($arguments[0] as $subFilter) {
                    $operation = array_shift($subFilter);
                    $processor = $processors[$operation] ?? null;
                    if ($processor === null) {
                        throw new \RuntimeException(sprintf('Filter operator "%s" is not supported.', $operation));
                    }
                    /* @var $processor QueryBuilderProcessor */
                    $select->where(...$processor->getAsWhereArguments($subFilter, $processors));
                }
            },
        ];
    }
}
