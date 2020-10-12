<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Psr\SimpleCache\CacheInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class SimpleCacheSchemaProvider implements SchemaProviderInterface
{
    public const DEFAULT_KEY = 'Cycle-ORM-Schema';
    private CacheInterface $cache;
    private string $key = self::DEFAULT_KEY;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function withConfig(array $config): self
    {
        $new = clone $this;
        $new->key = $config['key'] ?? self::DEFAULT_KEY;
        return $new;
    }

    public function read(): ?array
    {
        return $this->cache->get($this->key);
    }

    public function write(array $schema): bool
    {
        return $this->cache->set($this->key, $schema);
    }

    public function clear(): bool
    {
        return $this->cache->delete($this->key);
    }
    public function isWritable(): bool
    {
        return true;
    }
    public function isReadable(): bool
    {
        return true;
    }
}
