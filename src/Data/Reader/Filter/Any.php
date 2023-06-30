<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Filter;

use Cycle\ORM\Select\QueryBuilder;

final class Any extends Group
{
    public function getOperator(): string
    {
        return 'or';
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
                    /* @var $handler QueryBuilderFilter */
                    $select->orWhere(...$handler->getAsWhereArguments($subFilter, $handlers));
                }
            },
        ];
    }
}