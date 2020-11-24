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

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        $schema = $this->cache->get($this->key);

        if ($schema !== null || $nextProvider === null) {
            return $schema;
        }

        $schema = $nextProvider->read();
        if ($schema !== null) {
            $this->write($schema);
        }
        return $schema;
    }

    public function clear(): bool
    {
        $result = $this->cache->delete($this->key);
        if ($result === false) {
            throw new \RuntimeException("In the cache service was an error when deleting `{$this->key}` key.");
        }
        return true;
    }

    private function write(array $schema): bool
    {
        return $this->cache->set($this->key, $schema);
    }
}
