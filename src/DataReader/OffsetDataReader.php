<?php

namespace Yiisoft\Yii\Cycle\DataReader;

use Countable;
use Cycle\ORM\Select;
use InvalidArgumentException;
use Spiral\Database\Query\QueryInterface;
use Spiral\Pagination\PaginableInterface;
use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;
use Yiisoft\Yii\Cycle\DataReader\Cache\CachedCount;
use Yiisoft\Yii\Cycle\DataReader\Cache\CachedCollection;

final class OffsetDataReader implements DataReaderInterface, OffsetableDataInterface, CountableDataInterface
{
    /** @var QueryInterface|Select */
    private $query;
    private ?int $limit = null;
    private ?int $offset = null;
    private CachedCount $countCache;
    private CachedCollection $itemsCache;

    /**
     * @param Select|QueryInterface $query
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
    }
    public function withLimit(int $limit): self
    {
        $clone = clone $this;
        $clone->setLimit($limit);
        return $clone;
    }
    public function withOffset(int $offset): self
    {
        $clone = clone $this;
        $clone->setOffset($offset);
        return $clone;
    }
    public function count(): int
    {
        return $this->countCache->getCount();
    }
    public function read(): iterable
    {
        if ($this->itemsCache->getCollection() !== null) {
            return $this->itemsCache->getCollection();
        }
        $newQuery = clone $this->query;
        if ($this->offset !== null) {
            $newQuery->offset($this->offset);
        }
        if ($this->limit !== null) {
            $newQuery->limit($this->limit);
        }
        $this->itemsCache->setCollection($newQuery->fetchAll());
        return $this->itemsCache->getCollection();
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
}
