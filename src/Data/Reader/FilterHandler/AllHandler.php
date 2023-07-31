<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\FilterHandler;

use Cycle\ORM\Select\QueryBuilder;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Yii\Cycle\Data\Reader\QueryBuilderFilterHandler;

final class AllHandler extends GroupHandler
{
    public function getOperator(): string
    {
        return All::getOperator();
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
                    /** @var QueryBuilderFilterHandler $handler */
                    $select->where(...$handler->getAsWhereArguments($subFilter, $handlers));
                }
            },
        ];
    }
}
