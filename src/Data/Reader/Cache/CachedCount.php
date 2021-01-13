<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Cache;

use Countable;

final class CachedCount
{
    private ?int $count = null;
    private ?Countable $collection;

    public function __construct(Countable $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @psalm-internal Yiisoft\Yii\Cycle\Data\Reader
     */
    public function getCount(): int
    {
        return $this->count ?? $this->cacheCount();
    }

    private function cacheCount(): int
    {
        /** @psalm-suppress PossiblyNullReference */
        $this->count = $this->collection->count();
        $this->collection = null;
        return $this->count;
    }
}
