<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider;

use DateInterval;
use DateTime;
use InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

/**
 * ArrayCache provides caching for the current request only by storing the values in an array.
 *
 * See {@see \Psr\SimpleCache\CacheInterface} for common cache operations that ArrayCache supports.
 */
final class SimpleCacheService implements CacheInterface
{
    private const EXPIRATION_INFINITY = 0;
    private const EXPIRATION_EXPIRED = -1;

    private array $cache = [];
    public bool $returnOnDelete = true;

    public function __construct(array $cacheData = [])
    {
        $this->setMultiple($cacheData);
    }

    public function get($key, $default = null)
    {
        $this->validateKey($key);
        if (isset($this->cache[$key]) && !$this->isExpired($key)) {
            $value = $this->cache[$key][0];
            if (is_object($value)) {
                $value = clone $value;
            }

            return $value;
        }

        return $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->validateKey($key);
        $expiration = $this->ttlToExpiration($ttl);
        if ($expiration < 0) {
            return $this->delete($key);
        }
        if (is_object($value)) {
            $value = clone $value;
        }
        $this->cache[$key] = [$value, $expiration];
        return true;
    }

    public function delete($key): bool
    {
        $this->validateKey($key);
        unset($this->cache[$key]);
        return $this->returnOnDelete;
    }

    public function clear(): bool
    {
        $this->cache = [];
        return true;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        $results = [];
        foreach ($keys as $key) {
            assert(is_string($key));
            $value = $this->get($key, $default);
            $results[$key] = $value;
        }
        return $results;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $values = $this->iterableToArray($values);
        $this->validateKeysOfValues($values);
        foreach ($values as $key => $value) {
            $this->set((string)$key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple($keys): bool
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        foreach ($keys as $key) {
            assert(is_string($key));
            $this->delete($key);
        }
        return $this->returnOnDelete;
    }

    public function has($key): bool
    {
        $this->validateKey($key);
        return isset($this->cache[$key]) && !$this->isExpired($key);
    }

    /**
     * Checks whether item is expired or not
     *
     * @param string $key
     *
     * @return bool
     */
    private function isExpired(string $key): bool
    {
        return $this->cache[$key][1] !== 0 && $this->cache[$key][1] <= time();
    }

    /**
     * Converts TTL to expiration
     *
     * @param int|DateInterval|null $ttl
     *
     * @return int
     */
    private function ttlToExpiration($ttl): int
    {
        $ttl = $this->normalizeTtl($ttl);

        if ($ttl === null) {
            $expiration = static::EXPIRATION_INFINITY;
        } elseif ($ttl <= 0) {
            $expiration = static::EXPIRATION_EXPIRED;
        } else {
            $expiration = $ttl + time();
        }

        return $expiration;
    }

    /**
     * Normalizes cache TTL handling strings and {@see DateInterval} objects.
     *
     * @param int|string|DateInterval|null $ttl raw TTL.
     *
     * @return int|null TTL value as UNIX timestamp or null meaning infinity
     */
    private function normalizeTtl($ttl): ?int
    {
        if ($ttl instanceof DateInterval) {
            return (new DateTime('@0'))->add($ttl)->getTimestamp();
        }

        if (is_string($ttl)) {
            return (int)$ttl;
        }

        return $ttl;
    }

    /**
     * Converts iterable to array. If provided value is not iterable it throws an InvalidArgumentException
     *
     * @param $iterable
     *
     * @return array
     */
    private function iterableToArray($iterable): array
    {
        if (!is_iterable($iterable)) {
            throw new InvalidArgumentException('Iterable is expected, got ' . gettype($iterable));
        }

        return $iterable instanceof \Traversable ? iterator_to_array($iterable) : (array)$iterable;
    }

    private function validateKey($key): void
    {
        if (!\is_string($key) || strpbrk($key, '{}()/\@:')) {
            throw new InvalidArgumentException('Invalid key value.');
        }
    }

    private function validateKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $this->validateKey($key);
        }
    }

    private function validateKeysOfValues(array $values): void
    {
        $keys = array_map('strval', array_keys($values));
        $this->validateKeys($keys);
    }
}
