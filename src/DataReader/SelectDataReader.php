<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader;

use Closure;
use Countable;
use Cycle\ORM\Select;
use Cycle\ORM\Select\QueryBuilder;
use InvalidArgumentException;
use Spiral\Database\Query\SelectQuery;
use Spiral\Pagination\PaginableInterface;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\DataReader\Cache\CachedCount;
use Yiisoft\Yii\Cycle\DataReader\Cache\CachedCollection;
use Yiisoft\Yii\Cycle\DataReader\Processor;
use Yiisoft\Yii\Cycle\DataReader\Processor\QueryBuilderProcessor;

final class SelectDataReader implements DataReaderInterface
{
    /** @var Select|SelectQuery */
    private $query;
    private ?int $limit = null;
    private ?int $offset = null;
    private ?Sort $sorting = null;
    private ?FilterInterface $filter = null;
    private CachedCount $countCache;
    private CachedCollection $itemsCache;
    private CachedCollection $oneItemCache;
    /** @var FilterProcessorInterface[]|QueryBuilderProcessor[] */
    private array $filterProcessors = [];

    /**
     * @param Select|SelectQuery $query
     */
    public function __construct($query)
    {
        if (!$query instanceof Countable) {
            throw new InvalidArgumentException(sprintf('Query should implement %s interface', Countable::class));
        }
        if (!$query instanceof PaginableInterface) {
            throw new InvalidArgumentException(
                sprintf('Query should implement %s interface', PaginableInterface::class)
            );
        }
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

    public function withLimit(int $limit): self
    {
        $new = clone $this;
        $new->setLimit($limit);
        return $new;
    }

    public function withOffset(int $offset): self
    {
        $new = clone $this;
        $new->setOffset($offset);
        return $new;
    }

    public function withSort(?Sort $sorting): self
    {
        $new = clone $this;
        $new->setSort($sorting);
        return $new;
    }

    public function withFilter(FilterInterface $filter): self
    {
        $new = clone $this;
        $new->setFilter($filter);
        return $new;
    }

    public function withFilterProcessors(FilterProcessorInterface ...$filterProcessors): self
    {
        $new = clone $this;
        $new->setFilterProcessors(...$filterProcessors);
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
            $query = $this->buildQuery();
            $this->itemsCache->setCollection($query->fetchAll());
        }
        return $this->itemsCache->getCollection();
    }

    /**
     * @return mixed
     */
    public function readOne()
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
    public function getIterator(): \Generator
    {
        if ($this->itemsCache->getCollection() !== null) {
            yield from $this->itemsCache->getCollection();
        } else {
            yield from $this->buildQuery()->getIterator();
        }
    }

    /**
     * Convert to SQL string
     */
    public function __toString(): string
    {
        return $this->getSql();
    }

    public function getSql(): string
    {
        return $this->buildQuery()->sqlStatement();
    }

    private function setSort(?Sort $sorting): void
    {
        if ($this->sorting !== $sorting) {
            $this->sorting = $sorting;
            $this->itemsCache = new CachedCollection();
            $this->oneItemCache = new CachedCollection();
        }
    }

    private function setLimit(?int $limit): void
    {
        if ($this->limit !== $limit) {
            $this->limit = $limit;
            $this->itemsCache = new CachedCollection();
        }
    }

    private function setOffset(?int $offset): void
    {
        if ($this->offset !== $offset) {
            $this->offset = $offset;
            $this->itemsCache = new CachedCollection();
        }
    }

    private function setFilter(FilterInterface $filter): void
    {
        if ($this->filter !== $filter) {
            $this->filter = $filter;
            $this->itemsCache = new CachedCollection();
            $this->oneItemCache = new CachedCollection();
        }
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

    /**
     * @return Select|SelectQuery
     */
    private function buildQuery()
    {
        $newQuery = clone $this->query;
        if ($this->offset !== null) {
            $newQuery->offset($this->offset);
        }
        if ($this->sorting !== null) {
            $newQuery->orderBy($this->sorting->getOrder());
        }
        if ($this->limit !== null) {
            $newQuery->limit($this->limit);
        }
        if ($this->filter !== null) {
            $newQuery->andWhere($this->makeFilterClosure());
        }
        return $newQuery;
    }
    private function makeFilterClosure(): Closure
    {
        return function (QueryBuilder $select) {
            $filter = $this->filter->toArray();
            $operation = array_shift($filter);
            $arguments = $filter;

            $processor = $this->filterProcessors[$operation] ?? null;
            if ($processor === null) {
                throw new \RuntimeException(sprintf('Filter operator "%s" is not supported.', $operation));
            }
            $select->where(...$processor->getAsWhereArguments($arguments, $this->filterProcessors));
        };
    }
    private function resetCountCache(): void
    {
        $newQuery = clone $this->query;
        if ($this->filter !== null) {
            $newQuery->andWhere($this->makeFilterClosure());
        }
        $this->countCache = new CachedCount($newQuery);
    }
}
