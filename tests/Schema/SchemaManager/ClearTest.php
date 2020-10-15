<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\SchemaManager;

use Yiisoft\Yii\Cycle\Exception\CumulativeException;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ArraySchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\SameOriginProvider;

final class ClearTest extends BaseSchemaManagerTest
{
    public function testWithoutProviders(): void
    {
        $manager = $this->prepareSchemaManager([]);

        $manager->clear();

        // exception was not thrown
        $this->assertTrue(true);
    }

    public function testClearAllProvidersIndependentFromWriteable(): void
    {
        $writeable = new ArraySchemaProvider(static::SIMPLE_SCHEMA);
        $origin = new SameOriginProvider([]);
        $notReadable = $origin->withConfig([]);
        $notWriteable1 = (new SameOriginProvider(static::SIMPLE_SCHEMA))
            ->withConfig([SameOriginProvider::OPTION_WRITABLE => false]);
        $notWriteable2 = (new SameOriginProvider(static::ANOTHER_SCHEMA))
            ->withConfig([SameOriginProvider::OPTION_CLEARABLE => false]);

        $manager = $this->prepareSchemaManager([$writeable, $notWriteable1, $notReadable, $notWriteable2]);

        $manager->clear();

        $this->assertNull($writeable->read());
        $this->assertNull($notWriteable1->read());
        $this->assertNull($origin->read());
        $this->assertSame(static::ANOTHER_SCHEMA, $notWriteable2->read());
    }

    public function testWithExceptionWhenClear(): void
    {
        $provider1 = (new SameOriginProvider(static::SIMPLE_SCHEMA))
            ->withConfig([SameOriginProvider::EXCEPTION_ON_CLEAR => true]);
        $provider2 = new ArraySchemaProvider(static::ANOTHER_SCHEMA);
        $provider3 = new ArraySchemaProvider(static::ANOTHER_SCHEMA);
        $manager = $this->prepareSchemaManager([$provider1, $provider2, $provider3]);

        $this->expectException(CumulativeException::class);

        try {
            $manager->clear();
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            // first provider throws exception
            $this->assertSame(self::SIMPLE_SCHEMA, $provider1->read());
            // next provider will be cleared
            $this->assertNull($provider2->read());
            // last provider will be cleared
            $this->assertNull($provider3->read());
        }
    }
}
