<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider;

use RuntimeException;
use Yiisoft\Test\Support\SimpleCache\Action;
use Yiisoft\Test\Support\SimpleCache\MemorySimpleCache;
use Yiisoft\Test\Support\SimpleCache\SimpleCacheActionLogger;
use Yiisoft\Yii\Cycle\Schema\Provider\SimpleCacheSchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ArraySchemaProvider;

final class SimpleCacheSchemaProviderTest extends BaseSchemaProvider
{
    protected const READ_CONFIG = ['key' => self::CACHE_KEY];
    private const CACHE_KEY = 'test-cycle-schema-cache-key';

    private SimpleCacheActionLogger $cacheService;

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
        $this->assertSame([
            [Action::HAS, self::CACHE_KEY],
            [Action::DELETE, self::CACHE_KEY],
        ], $this->cacheService->getActionKeyList());
        $this->assertFalse($this->cacheService->has(self::CACHE_KEY));
    }

    public function testClearNotExistingKey(): void
    {
        $key = 'key-not-exists';
        $provider = $this->createSchemaProvider(['key' => $key]);

        $result = $provider->clear();

        $this->assertTrue($result);
        $this->assertSame([[Action::HAS, $key]], $this->cacheService->getActionKeyList());
    }

    public function testClearWithCacheOnDeleteError(): void
    {
        $provider = $this->createSchemaProvider(self::READ_CONFIG);
        $this->cacheService->getCacheService()->returnOnDelete = false;

        $this->expectException(RuntimeException::class);

        $provider->clear();
    }

    public function testWriteOnReadFromNextProvider(): void
    {
        $key = 'key-not-exists';
        $provider = $this->createSchemaProvider(['key' => $key]);
        $nextProvider = new ArraySchemaProvider(self::READ_CONFIG_SCHEMA);

        $result = $provider->read($nextProvider);
        $this->assertSame(
            [[Action::GET, $key], [Action::SET, $key]],
            $this->cacheService->getActionKeyList()
        );
        $this->assertSame(self::READ_CONFIG_SCHEMA, $result);
        $this->assertSame(self::READ_CONFIG_SCHEMA, $this->cacheService->get($key));
    }

    private function prepareCacheService(): void
    {
        $this->cacheService = new SimpleCacheActionLogger(
            new MemorySimpleCache(),
            [self::CACHE_KEY => self::READ_CONFIG_SCHEMA]
        );
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
