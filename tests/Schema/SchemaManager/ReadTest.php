<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\SchemaManager;

use RuntimeException;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ArraySchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\SameOriginProvider;

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
        ]);

        $manager = $this->prepareSchemaManager(['withoutSchema', 'withSchema']);

        $this->assertNotNull($manager->read());
    }

    public function testExceptionOnRead(): void
    {
        $this->prepareContainer([
            'withoutSchema' => (new SameOriginProvider(static::SIMPLE_SCHEMA))
                ->withConfig([SameOriginProvider::EXCEPTION_ON_READ => true]),
        ]);

        $this->expectException(RuntimeException::class);

        $this->prepareSchemaManager(['withoutSchema'])->read();
    }
}
