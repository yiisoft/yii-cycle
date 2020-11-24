<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider;

use InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use Yiisoft\Yii\Cycle\Schema\Provider\PhpFileSchemaProvider;
use Yiisoft\Yii\Cycle\Schema\Provider\SimpleCacheSchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ArraySchemaProvider;

final class SimpleCacheSchemaProviderTest extends BaseSchemaProviderTest
{
    protected const READ_CONFIG = ['key' => self::CACHE_KEY];
    private const CACHE_KEY = 'test-cycle-schema-cache-key';

    private SimpleCacheService $cacheService;

    public function testDefaultState(): void
    {
        $provider = $this->createSchemaProvider();

        $this->assertNull($provider->read());
        $this->assertTrue($this->cacheService->has(self::CACHE_KEY));
    }

    public function testClear(): void
    {
        $provider = $this->createSchemaProvider(self::READ_CONFIG);

        $result = $provider->clear();

        $this->assertTrue($result);
        $this->assertFalse($this->cacheService->has(self::CACHE_KEY));
    }

    public function testClearNotExistingKey(): void
    {
        $provider = $this->createSchemaProvider(['key' => 'key-not-exists']);

        $result = $provider->clear();

        $this->assertTrue($result);
    }

    public function testClearWithCacheOnDeleteError(): void
    {
        $provider = $this->createSchemaProvider();
        $this->cacheService->returnOnDelete = false;

        $this->expectException(RuntimeException::class);

        $provider->clear();
    }

    public function testWriteOnReadFromNextProvider(): void
    {
        $key = 'key-not-exists';
        $provider = $this->createSchemaProvider(['key' => $key]);
        $nextProvider = new ArraySchemaProvider(self::DEFAULT_CONFIG_SCHEMA);

        $result = $provider->read($nextProvider);
        $this->assertSame(self::DEFAULT_CONFIG_SCHEMA, $result);
        $this->assertSame(self::DEFAULT_CONFIG_SCHEMA, $this->cacheService->get($key));
    }

    // public function testClearNotFile(): void
    // {
    //     $provider = $this->createSchemaProvider(['file' => '@dir']);
    //
    //     $result = $provider->clear();
    //
    //     $this->assertFalse($result);
    // }

    private function prepareCacheService(): void
    {
        $this->cacheService = new SimpleCacheService([self::CACHE_KEY => self::DEFAULT_CONFIG_SCHEMA]);
    }

    protected function setUp(): void
    {
        $this->prepareCacheService();
    }

    protected function createSchemaProvider(array $config = null): SimpleCacheSchemaProvider
    {
        $provider = new SimpleCacheSchemaProvider($this->cacheService);
        return $config === null ? $provider : $provider->withConfig($config);
    }
}
