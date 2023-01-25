<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Processor;

use Cycle\ORM\Select\QueryBuilder;

final class All extends GroupProcessor
{
    public function getOperator(): string
    {
        return 'and';
    }

    public function getAsWhereArguments(array $arguments, array $handlers): array
    {
        $this->validateArguments($arguments);
        return [
            static function (QueryBuilder $select) use ($arguments, $handlers) {
                foreach ($arguments[0] as $subFilter) {
                    $operation = array_shift($subFilter);
                    $handler = $handlers[$operation] ?? null;
                    if ($handler === null) {
                        throw new \RuntimeException(sprintf('Filter operator "%s" is not supported.', $operation));
                    }
                    /* @var $handler QueryBuilderProcessor */
                    $select->where(...$handler->getAsWhereArguments($subFilter, $handlers));
                }
            },
        ];
    }
}
