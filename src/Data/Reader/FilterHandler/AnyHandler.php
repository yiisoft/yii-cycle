<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\FilterHandler;

use Cycle\ORM\Select\QueryBuilder;
use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Yii\Cycle\Data\Reader\QueryBuilderFilterHandler;

final class AnyHandler extends GroupHandler
{
    public function getOperator(): string
    {
        return Any::getOperator();
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
                    $select->orWhere(...$handler->getAsWhereArguments($subFilter, $handlers));
                }
            },
        ];
    }
}
