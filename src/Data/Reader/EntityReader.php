<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader;

use Closure;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\Select;
use Cycle\ORM\Select\QueryBuilder;
use Generator;
use InvalidArgumentException;
use RuntimeException;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\Cache\CachedCollection;
use Yiisoft\Yii\Cycle\Data\Reader\Cache\CachedCount;

/**
 * @template TKey as array-key
 * @template TValue as array|object
 *
 * @implements DataReaderInterface<TKey, TValue>
 */
final class EntityReader implements DataReaderInterface
{
    private Select|SelectQuery $query;
    private ?int $limit = null;
    private ?int $offset = null;
    private ?Sort $sorting = null;
    private ?FilterInterface $filter = null;
    private CachedCount $countCache;
    private CachedCollection $itemsCache;
    private CachedCollection $oneItemCache;
    /** @var FilterHandlerInterface[]|QueryBuilderFilterHandler[] */
    private array $filterHandlers = [];

    public function __construct(Select|SelectQuery $query)
    {
        $this->query = clone $query;
        $this->countCache = new CachedCount($this->query);
        $this->itemsCache = new CachedCollection();
        $this->oneItemCache = new CachedCollection();
        $this->setFilterHandlers(
            new FilterHandler\AllHandler(),
            new FilterHandler\AnyHandler(),
            new FilterHandler\EqualsHandler(),
            new FilterHandler\GreaterThanHandler(),
            new FilterHandler\GreaterThanOrEqualHandler(),
            new FilterHandler\InHandler(),
            new FilterHandler\LessThanHandler(),
            new FilterHandler\LessThanOrEqualHandler(),
            new FilterHandler\LikeHandler(),
            // new Processor\Not()
        );
    }

    public function getSort(): ?Sort
    {
        return $this->sorting;
    }

    /**
     * @psalm-mutation-free
     */
    public function withLimit(int $limit): static
    {
        if ($limit < 0) {
            throw new InvalidArgumentException('$limit must not be less than 0.');
        }
        $new = clone $this;
        if ($new->limit !== $limit) {
            $new->limit = $limit;
            $new->itemsCache = new CachedCollection();
        }
        return $new;
    }

    /**
     * @psalm-mutation-free
     */
    public function withOffset(int $offset): static
    {
        $new = clone $this;
        if ($new->offset !== $offset) {
            $new->offset = $offset;
            $new->itemsCache = new CachedCollection();
        }
        return $new;
    }

    /**
     * @psalm-mutation-free
     */
    public function withSort(?Sort $sort): static
    {
        $new = clone $this;
        if ($new->sorting !== $sort) {
            $new->sorting = $sort;
            $new->itemsCache = new CachedCollection();
            $new->oneItemCache = new CachedCollection();
        }
        return $new;
    }

    /**
     * @psalm-mutation-free
     */
    public function withFilter(FilterInterface $filter): static
    {
        $new = clone $this;
        if ($new->filter !== $filter) {
            $new->filter = $filter;
            $new->itemsCache = new CachedCollection();
            $new->oneItemCache = new CachedCollection();
            /** @psalm-suppress ImpureMethodCall */
            $new->resetCountCache();
        }
        return $new;
    }

    /**
     * @psalm-mutation-free
     */
    public function withFilterHandlers(FilterHandlerInterface ...$filterHandlers): static
    {
        $new = clone $this;
        /** @psalm-suppress ImpureMethodCall */
        $new->setFilterHandlers(...$filterHandlers);
        /** @psalm-suppress ImpureMethodCall */
        $new->resetCountCache();
        $new->itemsCache = new CachedCollection();
        $new->oneItemCache = new CachedCollection();
        return $new;
    }

    public function count(): int
    {
        return $this->countCache->getCount();
    }

    public function read(): iterable
    {
        if ($this->itemsCache->getCollection() === null) {
            $query = $this->buildSelectQuery();
            $this->itemsCache->setCollection($query->fetchAll());
        }
        return $this->itemsCache->getCollection();
    }

    public function readOne(): null|array|object
    {
        if (!$this->oneItemCache->isCollected()) {
            $item = $this->itemsCache->isCollected()
                // get first item from cached collection
                ? $this->itemsCache->getGenerator()->current()
                // read data with limit 1
                : $this->withLimit(1)->getIterator()->current();
            $this->oneItemCache->setCollection($item === null ? [] : [$item]);
        }

        return $this->oneItemCache->getGenerator()->current();
    }

    /**
     * Get Iterator without caching
     */
    public function getIterator(): Generator
    {
        yield from $this->itemsCache->getCollection() ?? $this->buildSelectQuery()->getIterator();
    }

    public function getSql(): string
    {
        $query = $this->buildSelectQuery();
        return (string)($query instanceof Select ? $query->buildQuery() : $query);
    }

    private function setFilterHandlers(FilterHandlerInterface ...$filterHandlers): void
    {
        $handlers = [];
        foreach ($filterHandlers as $filterHandler) {
            if ($filterHandler instanceof QueryBuilderFilterHandler) {
                $handlers[$filterHandler->getOperator()] = $filterHandler;
            }
        }
        $this->filterHandlers = array_merge($this->filterHandlers, $handlers);
    }

    private function buildSelectQuery(): SelectQuery|Select
    {
        $newQuery = clone $this->query;
        if ($this->offset !== null) {
            $newQuery->offset($this->offset);
        }
        if ($this->sorting !== null) {
            $newQuery->orderBy($this->normalizeSortingCriteria($this->sorting->getCriteria()));
        }
        if ($this->limit !== null) {
            $newQuery->limit($this->limit);
        }
        if ($this->filter !== null) {
            $newQuery->andWhere($this->makeFilterClosure($this->filter));
        }
        return $newQuery;
    }

    private function makeFilterClosure(FilterInterface $filter): Closure
    {
        return function (QueryBuilder $select) use ($filter) {
            $filterArray = $filter->toCriteriaArray();
            $operation = array_shift($filterArray);
            $arguments = $filterArray;

            if (!array_key_exists($operation, $this->filterHandlers)) {
                throw new RuntimeException(sprintf('Filter operator "%s" is not supported.', $operation));
            }
            /** @var QueryBuilderFilterHandler $handler */
            $handler = $this->filterHandlers[$operation];
            $select->where(...$handler->getAsWhereArguments($arguments, $this->filterHandlers));
        };
    }

    private function resetCountCache(): void
    {
        $newQuery = clone $this->query;
        if ($this->filter !== null) {
            $newQuery->andWhere($this->makeFilterClosure($this->filter));
        }
        $this->countCache = new CachedCount($newQuery);
    }

    private function normalizeSortingCriteria(array $criteria): array
    {
        foreach ($criteria as $field => $direction) {
            if (is_int($direction)) {
                $direction = match ($direction) {
                    SORT_DESC => 'DESC',
                    default => 'ASC',
                };
            }
            $criteria[$field] = $direction;
        }

        return $criteria;
    }
}
