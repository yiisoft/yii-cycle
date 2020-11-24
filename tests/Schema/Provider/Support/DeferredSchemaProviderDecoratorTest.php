<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider\Support;

use Cycle\ORM\Schema;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Schema\Provider\Support\DeferredSchemaProviderDecorator;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;
use Yiisoft\Yii\Cycle\Tests\Schema\Provider\BaseSchemaProviderTest;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ArraySchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ConfigurableSchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\SameOriginProvider;

final class DeferredSchemaProviderDecoratorTest extends BaseSchemaProviderTest
{
    protected const READ_CONFIG = self::READ_CONFIG_SCHEMA;
    protected const ANOTHER_SCHEMA = [
        'post' => [
            Schema::ENTITY => \stdClass::class,
            Schema::MAPPER => \stdClass::class,
        ],
    ];
    protected const ALIASES = [
        '@test' => 'test',
    ];

    protected ContainerInterface $container;

    public function testReadWithLatestProvider()
    {
        $provider1 = new ArraySchemaProvider(null);
        $provider2 = new ArraySchemaProvider(null);
        $latestProvider = new ArraySchemaProvider(self::ANOTHER_SCHEMA);

        $deferred1 = $this->createSchemaProvider(null, $provider1);
        $deferred2 = $this->createSchemaProvider(null, $provider2, $deferred1);

        $result = $deferred2->read($latestProvider);

        $this->assertSame(self::ANOTHER_SCHEMA, $result);
        $this->assertSame(self::ANOTHER_SCHEMA, $provider1->read());
        $this->assertSame(self::ANOTHER_SCHEMA, $provider2->read());
    }

    public function testNextDeferredProviderImmutabilityOnReadWithLatestProvider()
    {
        $this->prepareContainer([
            SameOriginProvider::class => (new SameOriginProvider(null))
                ->withConfig([SameOriginProvider::OPTION_WRITABLE => false]),
        ]);
        $latestProvider = new ArraySchemaProvider(self::ANOTHER_SCHEMA);

        $deferred1 = $this->createSchemaProvider(null, SameOriginProvider::class);
        $deferred2 = $this->createSchemaProvider(null, SameOriginProvider::class, $deferred1);

        $result1 = $deferred2->read($latestProvider);
        $result2 = $deferred2->read();

        $this->assertSame(self::ANOTHER_SCHEMA, $result1);
        $this->assertNull($result2);
    }

    public function testUseSameProviderIfLatestProviderIsExists()
    {
        $provider1 = new ConfigurableSchemaProvider(null);
        $this->prepareContainer([ConfigurableSchemaProvider::class => $provider1]);
        $latestProvider = new ArraySchemaProvider(self::ANOTHER_SCHEMA);

        $deferred1 = $this->createSchemaProvider(
            [ConfigurableSchemaProvider::OPTION_WRITABLE => true],
            ConfigurableSchemaProvider::class
        );
        $deferred2 = $this->createSchemaProvider(null, null, $deferred1);

        $result1 = $deferred2->read($latestProvider);
        $result2 = $deferred1->read();

        $this->assertSame(self::ANOTHER_SCHEMA, $result1);
        $this->assertSame(self::ANOTHER_SCHEMA, $result2);
    }

    protected function setUp(): void
    {
        $this->prepareContainer();
    }

    protected function prepareContainer(array $definitions = []): ContainerInterface
    {
        return $this->container = new SimpleContainer(array_merge([
            ArraySchemaProvider::class => new ArraySchemaProvider(self::ANOTHER_SCHEMA),
        ], $definitions));
    }

    /**
     * @param SchemaProviderInterface|string|null $provider
     */
    protected function createSchemaProvider(
        ?array $config = null,
        $provider = null,
        ?DeferredSchemaProviderDecorator $nextProvider = null
    ): DeferredSchemaProviderDecorator {
        if ($provider === null) {
            $provider = new ArraySchemaProvider(self::DEFAULT_SCHEMA);
        }
        $provider = new DeferredSchemaProviderDecorator($this->container, $provider, $nextProvider);
        return $config === null ? $provider : $provider->withConfig($config);
    }
}
