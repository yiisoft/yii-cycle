<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Reader\Cache;

final class CachedCollection
{
    private ?iterable $collection = null;

    public function setCollection(iterable $collection): void
    {
        $this->collection = $collection;
    }

    public function isCollected(): bool
    {
        return $this->collection !== null;
    }

    /**
     * @psalm-ignore-nullable-return
     */
    public function getCollection(): ?iterable
    {
        return $this->collection;
    }

    public function getGenerator(): \Generator
    {
        if ($this->collection !== null) {
            yield from $this->collection;
        }
    }
}
