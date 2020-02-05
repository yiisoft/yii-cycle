<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader\Cache;

final class CachedCollection
{
    private ?iterable $collection = null;
    public function setCollection(iterable $collection): void
    {
        $this->collection = $collection;
    }

    public function getCollection(): ?iterable
    {
        return $this->collection;
    }
}
