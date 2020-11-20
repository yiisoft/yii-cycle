<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader\Processor;

use Cycle\ORM\Select\QueryBuilder;

final class Any extends GroupProcessor
{
    public function getOperator(): string
    {
        return 'or';
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
                    $select->orWhere(...$processor->getAsWhereArguments($subFilter, $processors));
                }
            },
        ];
    }
}
