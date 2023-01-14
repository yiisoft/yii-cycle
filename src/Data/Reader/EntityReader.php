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
use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\Cache\CachedCollection;
use Yiisoft\Yii\Cycle\Data\Reader\Cache\CachedCount;
use Yiisoft\Yii\Cycle\Data\Reader\Processor\QueryBuilderProcessor;

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
    /** @var FilterProcessorInterface[]|QueryBuilderProcessor[] */
    private array $filterProcessors = [];

    public function __construct(Select|SelectQuery $query)
    {
        $this->query = clone $query;
        $this->countCache = new CachedCount($this->query);
        $this->itemsCache = new CachedCollection();
        $this->oneItemCache = new CachedCollection();
        $this->setFilterProcessors(
            new Processor\All(),
            new Processor\Any(),
            new Processor\Equals(),
            new Processor\GreaterThan(),
            new Processor\GreaterThanOrEqual(),
            new Processor\In(),
            new Processor\LessThan(),
            new Processor\LessThanOrEqual(),
            new Processor\Like(),
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
        }
        return $new;
    }

    /**
     * @psalm-mutation-free
     */
    public function withFilterProcessors(FilterProcessorInterface ...$filterProcessors): static
    {
        $new = clone $this;
        /** @psalm-suppress ImpureMethodCall */
        $new->setFilterProcessors(...$filterProcessors);
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

    private function setFilterProcessors(FilterProcessorInterface ...$filterProcessors): void
    {
        $processors = [];
        foreach ($filterProcessors as $filterProcessor) {
            if ($filterProcessor instanceof QueryBuilderProcessor) {
                $processors[$filterProcessor->getOperator()] = $filterProcessor;
            }
        }
        $this->filterProcessors = array_merge($this->filterProcessors, $processors);
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
            $filterArray = $filter->toArray();
            $operation = array_shift($filterArray);
            $arguments = $filterArray;

            if (!array_key_exists($operation, $this->filterProcessors)) {
                throw new RuntimeException(sprintf('Filter operator "%s" is not supported.', $operation));
            }
            /** @psalm-var QueryBuilderProcessor $processor */
            $processor = $this->filterProcessors[$operation];
            $select->where(...$processor->getAsWhereArguments($arguments, $this->filterProcessors));
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
