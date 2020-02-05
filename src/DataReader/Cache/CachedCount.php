<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\DataReader\Cache;

use Countable;

final class CachedCount
{
    private ?int $count = null;
    private ?Countable $collection;
    public function __construct(Countable $collection)
    {
        $this->collection = $collection;
    }
    public function getCount(): int
    {
        if ($this->count === null) {
            $this->count = (int) $this->collection->count();
            $this->collection = null;
        }
        return $this->count;
    }
}
