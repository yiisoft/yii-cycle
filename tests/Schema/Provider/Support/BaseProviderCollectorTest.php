<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider\Support;

use Cycle\ORM\SchemaInterface as Schema;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;
use Yiisoft\Yii\Cycle\Exception\CumulativeException;
use Yiisoft\Yii\Cycle\Tests\Schema\Provider\BaseSchemaProviderTest;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ArraySchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ConfigurableSchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\SameOriginProvider;

abstract class BaseProviderCollectorTest extends BaseSchemaProviderTest
{
    protected const ANOTHER_SCHEMA = [
        'post' => [
            Schema::ENTITY => \stdClass::class,
            Schema::MAPPER => \stdClass::class,
        ],
    ];

    protected ContainerInterface $container;

    protected function setUp(): void
    {
        $this->prepareContainer();
    }

    protected function prepareContainer(array $definitions = []): ContainerInterface
    {
        return $this->container = new SimpleContainer(array_merge([
            ArraySchemaProvider::class => new ArraySchemaProvider(),
        ], $definitions));
    }

    // Definition resolving test

    public function testProviderFromString(): void
    {
        $this->prepareContainer(['provider' => new ArraySchemaProvider(self::READ_CONFIG_SCHEMA)]);

        $provider = $this->createSchemaProvider(['provider']);

        $this->assertSame(self::READ_CONFIG_SCHEMA, $provider->read());
    }

    public function testProviderFromArray(): void
    {
        $this->prepareContainer(['provider' => new ArraySchemaProvider(self::READ_CONFIG_SCHEMA)]);
        $newSchema = self::ANOTHER_SCHEMA;

        $provider = $this->createSchemaProvider(['provider' => $newSchema]);

        $this->assertSame($newSchema, $provider->read());
    }

    public function testIgnoreStringKeyIfDefinitionIsNotArray(): void
    {
        $this->prepareContainer(['provider' => new ArraySchemaProvider(self::READ_CONFIG_SCHEMA)]);

        $provider = $this->createSchemaProvider(['provider' => new ArraySchemaProvider(self::ANOTHER_SCHEMA)]);

        $this->assertSame(self::ANOTHER_SCHEMA, $provider->read());
    }

    public function testProviderAsObject(): void
    {
        $provider = $this->createSchemaProvider([new ArraySchemaProvider(self::READ_CONFIG_SCHEMA)]);

        $this->assertSame(self::READ_CONFIG_SCHEMA, $provider->read());
    }

    public function testProviderAsBadClassObject(): void
    {
        $provider = $this->createSchemaProvider([new \DateTimeImmutable()]);

        $this->expectException(BadDeclarationException::class);

        $provider->read();
    }

    // Clear test

    public function testClearWithoutProviders(): void
    {
        $provider = $this->createSchemaProvider([]);

        $result = $provider->clear();

        // exception was not thrown
        $this->assertFalse($result);
    }

    public function testClearNotConfigured(): void
    {
        $provider = $this->createSchemaProvider(null);

        $this->expectException(RuntimeException::class);

        $provider->clear();
    }

    public function testClearResultFalse(): void
    {
        $provider1 = (new ConfigurableSchemaProvider(self::ANOTHER_SCHEMA))
            ->withConfig([ConfigurableSchemaProvider::OPTION_CLEARABLE => false]);
        $provider2 = (new ConfigurableSchemaProvider(self::ANOTHER_SCHEMA))
            ->withConfig([ConfigurableSchemaProvider::OPTION_CLEARABLE => false]);
        $provider3 = (new ConfigurableSchemaProvider(self::ANOTHER_SCHEMA))
            ->withConfig([ConfigurableSchemaProvider::OPTION_CLEARABLE => false]);

        $provider = $this->createSchemaProvider([$provider1, $provider2, $provider3]);

        $result = $provider->clear();

        // exception was not thrown
        $this->assertFalse($result);
    }

    public function testClearResultTrue(): void
    {
        $provider1 = (new ConfigurableSchemaProvider(self::ANOTHER_SCHEMA))
            ->withConfig([ConfigurableSchemaProvider::OPTION_CLEARABLE => false]);
        $provider2 = (new ConfigurableSchemaProvider(self::ANOTHER_SCHEMA))
            ->withConfig([ConfigurableSchemaProvider::OPTION_CLEARABLE => true]);
        $provider3 = (new ConfigurableSchemaProvider(self::ANOTHER_SCHEMA))
            ->withConfig([ConfigurableSchemaProvider::OPTION_CLEARABLE => false]);

        $provider = $this->createSchemaProvider([$provider1, $provider2, $provider3]);

        $result = $provider->clear();

        // exception was not thrown
        $this->assertTrue($result);
    }

    public function testClearAllProvidersIndependentFromWriteable(): void
    {
        $writeable = new ArraySchemaProvider(self::READ_CONFIG_SCHEMA);
        $origin = new SameOriginProvider([]);
        $notReadable = $origin->withConfig([]);
        $notWriteable1 = (new SameOriginProvider(self::READ_CONFIG_SCHEMA))
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
        $provider1 = (new SameOriginProvider(self::READ_CONFIG_SCHEMA))
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
            $this->assertSame(self::READ_CONFIG_SCHEMA, $provider1->read());
            // next provider will be cleared
            $this->assertNull($provider2->read());
            // last provider will be cleared
            $this->assertNull($provider3->read());
        }
    }

    // Reading test

    public function testReadNotConfigured(): void
    {
        $provider = $this->createSchemaProvider(null);

        $this->expectException(RuntimeException::class);

        $provider->read();
    }

    public function testReadWithoutProviders(): void
    {
        $provider = $this->createSchemaProvider([]);

        $this->assertNull($provider->read());
    }

    public function testReadWithException(): void
    {
        $this->prepareContainer([
            'withoutSchema' => (new SameOriginProvider(self::READ_CONFIG_SCHEMA))
                ->withConfig([SameOriginProvider::EXCEPTION_ON_READ => true]),
        ]);

        $this->expectException(RuntimeException::class);

        $this->createSchemaProvider(['withoutSchema'])->read();
    }

    public function testReadFromOneProvider(): void
    {
        $provider = $this->createSchemaProvider([new ArraySchemaProvider(self::READ_CONFIG_SCHEMA)]);

        $schema = $provider->read();

        $this->assertSame(self::READ_CONFIG_SCHEMA, $schema);
    }

    public function testReadingOrderWithNullIgnoring(): void
    {
        $this->prepareContainer([
            'withSchema' => new ArraySchemaProvider(self::READ_CONFIG_SCHEMA),
            'withoutSchema' => new ArraySchemaProvider(null),
        ]);

        $provider = $this->createSchemaProvider(['withoutSchema', 'withSchema']);

        $this->assertNotNull($provider->read());
    }

    public function testReadWithAlternativeProvider(): void
    {
        $provider = $this->createSchemaProvider([
            new ArraySchemaProvider(),
            new ArraySchemaProvider(),
            new ArraySchemaProvider(),
        ]);

        $schema1 = $provider->read();
        $schema2 = $provider->read(new ArraySchemaProvider(self::READ_CONFIG_SCHEMA));

        $this->assertNull($schema1);
        $this->assertSame(self::READ_CONFIG_SCHEMA, $schema2);
    }

    public function testReadWithoutProvidersButWithAlternativeProvider(): void
    {
        $provider = $this->createSchemaProvider();

        $schema2 = $provider->read(new ArraySchemaProvider(self::READ_CONFIG_SCHEMA));

        $this->assertSame(self::READ_CONFIG_SCHEMA, $schema2);
    }
}
