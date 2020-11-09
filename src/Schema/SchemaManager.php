<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

/**
 * @deprecated moved to {@see \Yiisoft\Yii\Cycle\Schema\Provider\SchemaProviderPipeline}
 */
final class SchemaManager implements SchemaProviderInterface
{
    private SchemaProviderInterface $provider;

    public function __construct(SchemaProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        return $this->provider->read();
    }

    public function clear(): bool
    {
        return $this->provider->clear();
    }
    public function withConfig(array $config): SchemaProviderInterface
    {
        return clone $this;
    }
}
