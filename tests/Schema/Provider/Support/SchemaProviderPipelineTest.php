<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider\Support;

use Cycle\ORM\Schema;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;
use Yiisoft\Yii\Cycle\Exception\CumulativeException;
use Yiisoft\Yii\Cycle\Schema\Provider\Support\SchemaProviderPipeline;
use Yiisoft\Yii\Cycle\Tests\Schema\Provider\BaseSchemaProviderTest;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ArraySchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\SameOriginProvider;

final class SchemaProviderPipelineTest extends BaseSchemaProviderTest
{
    protected const READ_CONFIG = [
        ArraySchemaProvider::class => self::DEFAULT_CONFIG_SCHEMA,
    ];
    protected const DEFAULT_CONFIG_SCHEMA = [
        'user' => [
            Schema::ENTITY => \stdClass::class,
            Schema::MAPPER => \stdClass::class,
            Schema::DATABASE => 'default',
            Schema::TABLE => 'user',
            Schema::PRIMARY_KEY => 'id',
            Schema::COLUMNS => [
                'id' => 'id',
                'email' => 'email',
                'balance' => 'balance',
            ],
            Schema::TYPECAST => [
                'id' => 'int',
                'balance' => 'float',
            ],
            Schema::RELATIONS => [],
        ],
    ];
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

    protected function setUp(): void
    {
        $this->prepareContainer();
    }

    protected function prepareContainer(array $definitions = []): ContainerInterface
    {
        return $this->container = new SimpleContainer(array_merge([
            Aliases::class => new Aliases(self::ALIASES),
            ArraySchemaProvider::class => new ArraySchemaProvider(),
        ], $definitions));
    }

    protected function createSchemaProvider(?array $config = []): SchemaProviderPipeline
    {
        $provider = new SchemaProviderPipeline($this->container);
        return $config === null ? $provider : $provider->withConfig($config);
    }

    // Clear test

    public function testClearWithoutProviders(): void
    {
        $provider = $this->createSchemaProvider([]);

        $provider->clear();

        // exception was not thrown
        $this->assertTrue(true);
    }

    public function testClearNotConfigured(): void
    {
        $provider = $this->createSchemaProvider(null);

        $this->expectException(RuntimeException::class);

        $provider->clear();
    }

    public function testClearAllProvidersIndependentFromWriteable(): void
    {
        $writeable = new ArraySchemaProvider(self::DEFAULT_CONFIG_SCHEMA);
        $origin = new SameOriginProvider([]);
        $notReadable = $origin->withConfig([]);
        $notWriteable1 = (new SameOriginProvider(self::DEFAULT_CONFIG_SCHEMA))
            ->withConfig([SameOriginProvider::OPTION_WRITABLE => false]);
        $notWriteable2 = (new SameOriginProvider(self::ANOTHER_SCHEMA))
            ->withConfig([SameOriginProvider::OPTION_CLEARABLE => false]);

        $provider = $this->createSchemaProvider([$writeable, $notWriteable1, $notReadable, $notWriteable2]);

        $provider->clear();

        $this->assertNull($writeable->read());
        $this->assertNull($notWriteable1->read());
        $this->assertNull($origin->read());
        $this->assertSame(self::ANOTHER_SCHEMA, $notWriteable2->read());
    }

    public function testClearWithException(): void
    {
        $provider1 = (new SameOriginProvider(self::DEFAULT_CONFIG_SCHEMA))
            ->withConfig([SameOriginProvider::EXCEPTION_ON_CLEAR => true]);
        $provider2 = new ArraySchemaProvider(self::ANOTHER_SCHEMA);
        $provider3 = new ArraySchemaProvider(self::ANOTHER_SCHEMA);
        $provider = $this->createSchemaProvider([$provider1, $provider2, $provider3]);

        $this->expectException(CumulativeException::class);

        try {
            $provider->clear();
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            // first provider throws exception
            $this->assertSame(self::DEFAULT_CONFIG_SCHEMA, $provider1->read());
            // next provider will be cleared
            $this->assertNull($provider2->read());
            // last provider will be cleared
            $this->assertNull($provider3->read());
        }
    }

    // Definition resolving test

    public function testProviderFromString(): void
    {
        $this->prepareContainer(['provider' => new ArraySchemaProvider(self::DEFAULT_CONFIG_SCHEMA)]);

        $provider = $this->createSchemaProvider(['provider']);

        $this->assertSame(self::DEFAULT_CONFIG_SCHEMA, $provider->read());
    }

    public function testProviderFromArray(): void
    {
        $this->prepareContainer(['provider' => new ArraySchemaProvider(self::DEFAULT_CONFIG_SCHEMA)]);
        $newSchema = self::ANOTHER_SCHEMA;

        $provider = $this->createSchemaProvider(['provider' => $newSchema]);

        $this->assertSame($newSchema, $provider->read());
    }

    public function testIgnoreStringKeyIfDefinitionIsNotArray(): void
    {
        $this->prepareContainer(['provider' => new ArraySchemaProvider(self::DEFAULT_CONFIG_SCHEMA)]);

        $provider = $this->createSchemaProvider(['provider' => new ArraySchemaProvider(self::ANOTHER_SCHEMA)]);

        $this->assertSame(self::ANOTHER_SCHEMA, $provider->read());
    }

    public function testProviderAsObject(): void
    {
        $provider = $this->createSchemaProvider([new ArraySchemaProvider(self::DEFAULT_CONFIG_SCHEMA)]);

        $this->assertSame(self::DEFAULT_CONFIG_SCHEMA, $provider->read());
    }

    public function testProviderAsBadClassObject(): void
    {
        $provider = $this->createSchemaProvider([new \DateTimeImmutable()]);

        $this->expectException(BadDeclarationException::class);

        $provider->read();
    }

    public function testShortCircuitInstantiation(): void
    {
        $this->prepareContainer([
            'goodProvider' => new ArraySchemaProvider(self::DEFAULT_CONFIG_SCHEMA),
            'badProvider' => 'not an object',
        ]);

        $provider = $this->createSchemaProvider(['goodProvider', 'badProvider', 'undefined provider']);

        $this->assertSame(self::DEFAULT_CONFIG_SCHEMA, $provider->read());
    }

    // Reading test

    public function testReadWithoutProviders(): void
    {
        $provider = $this->createSchemaProvider([]);

        $this->assertNull($provider->read());
    }

    public function testReadFromOneProvider(): void
    {
        $provider = $this->createSchemaProvider([new ArraySchemaProvider(self::DEFAULT_CONFIG_SCHEMA)]);

        $schema = $provider->read();

        $this->assertSame(self::DEFAULT_CONFIG_SCHEMA, $schema);
    }

    public function testReadingOrderWithNullIgnoring(): void
    {
        $this->prepareContainer([
            'withSchema' => new ArraySchemaProvider(self::DEFAULT_CONFIG_SCHEMA),
            'withoutSchema' => new ArraySchemaProvider(null),
        ]);

        $provider = $this->createSchemaProvider(['withoutSchema', 'withSchema']);

        $this->assertNotNull($provider->read());
    }

    public function testReadWithException(): void
    {
        $this->prepareContainer([
            'withoutSchema' => (new SameOriginProvider(self::DEFAULT_CONFIG_SCHEMA))
                ->withConfig([SameOriginProvider::EXCEPTION_ON_READ => true]),
        ]);

        $this->expectException(RuntimeException::class);

        $this->createSchemaProvider(['withoutSchema'])->read();
    }

    public function testReadWithLatestProvider(): void
    {
        $provider = $this->createSchemaProvider([
            new ArraySchemaProvider(),
            new ArraySchemaProvider(),
            new ArraySchemaProvider(),
        ]);

        $schema1 = $provider->read();
        $schema2 = $provider->read(new ArraySchemaProvider(self::DEFAULT_CONFIG_SCHEMA));

        $this->assertNull($schema1);
        $this->assertSame(self::DEFAULT_CONFIG_SCHEMA, $schema2);
    }

    public function testReadNotConfigured(): void
    {
        $provider = $this->createSchemaProvider(null);

        $this->expectException(RuntimeException::class);

        $provider->read();
    }
}
