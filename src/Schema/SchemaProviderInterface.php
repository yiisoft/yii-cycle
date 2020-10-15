<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

interface SchemaProviderInterface
{
    public function withConfig(array $config): self;

    public function read(?SchemaProviderInterface $nextProvider = null): ?array;

    public function clear(): bool;
}
