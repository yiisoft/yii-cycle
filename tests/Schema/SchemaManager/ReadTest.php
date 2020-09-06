<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\SchemaManager;

use Yiisoft\Yii\Cycle\Tests\Schema\SchemaManager\Stub\ArraySchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\SchemaManager\Stub\SameOriginProvider;

class ReadTest extends BaseSchemaManagerTest
{
    public function testWithoutProviders(): void
    {
        $manager = $this->prepareSchemaManager([]);

        $this->assertNull($manager->read());
    }

    public function testReadFromOneProvider(): void
    {
        $manager = $this->prepareSchemaManager([new ArraySchemaProvider(static::SIMPLE_SCHEMA)]);

        $schema = $manager->read();

        $this->assertSame(self::SIMPLE_SCHEMA, $schema);
    }

    public function testReadingOrderWithNullIgnoring(): void
    {
        $this->prepareContainer([
            'withSchema' => new ArraySchemaProvider(self::SIMPLE_SCHEMA),
            'withoutSchema' => new ArraySchemaProvider(null),
            'emptySchema' => new ArraySchemaProvider([]),
        ]);

        $manager = $this->prepareSchemaManager(['withoutSchema', 'emptySchema', 'withSchema']);

        $this->assertSame([], $manager->read());
    }

    public function testSkipNotReadableProviders(): void
    {
        $manager = $this->prepareSchemaManager([
            (new SameOriginProvider(self::SIMPLE_SCHEMA))->withConfig([SameOriginProvider::OPTION_READABLE => false]),
            new ArraySchemaProvider(self::ANOTHER_SCHEMA),
        ]);

        $this->assertSame(self::ANOTHER_SCHEMA, $manager->read());
    }

    public function testWriteSchemaToSkippedAndEmptyProviders(): void
    {
        $origin = new SameOriginProvider(self::SIMPLE_SCHEMA);
        $notReadable = $origin->withConfig([SameOriginProvider::OPTION_READABLE => false]);
        $emptyProvider = new ArraySchemaProvider(null);

        $manager = $this->prepareSchemaManager([
            $emptyProvider,
            $notReadable,
            new ArraySchemaProvider(self::ANOTHER_SCHEMA),
        ]);

        $this->assertSame(self::ANOTHER_SCHEMA, $manager->read());
        $this->assertSame(self::ANOTHER_SCHEMA, $origin->read());
        $this->assertSame(self::ANOTHER_SCHEMA, $emptyProvider->read());
    }

    public function testNullSchemaNotWrittenToSkippedProviders(): void
    {
        $origin = new SameOriginProvider(self::SIMPLE_SCHEMA);
        $notReadable = $origin->withConfig([SameOriginProvider::OPTION_READABLE => false]);
        $emptyProvider = new ArraySchemaProvider(null);

        $manager = $this->prepareSchemaManager([
            $emptyProvider,
            $notReadable,
            new ArraySchemaProvider(null),
        ]);

        $this->assertNull($manager->read());
        $this->assertNull($emptyProvider->read());
        $this->assertSame(self::SIMPLE_SCHEMA, $origin->read());
    }

    public function testSchemaInNotWriteableProvidersIsNotRewritten(): void
    {
        $origin = new SameOriginProvider(null);
        $notWriteable = $origin->withConfig([
            SameOriginProvider::OPTION_WRITABLE => false,
            SameOriginProvider::EXCEPTION_ON_WRITE => true,
        ]);

        $manager = $this->prepareSchemaManager([
            $notWriteable,
            new ArraySchemaProvider(self::ANOTHER_SCHEMA),
        ]);

        $this->assertSame(self::ANOTHER_SCHEMA, $manager->read());
        $this->assertNull($origin->read());
    }
}
