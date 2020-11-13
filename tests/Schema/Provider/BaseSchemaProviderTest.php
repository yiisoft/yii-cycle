<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ArraySchemaProvider;

abstract class BaseSchemaProviderTest extends TestCase
{
    protected const READ_CONFIG = [];
    protected const DEFAULT_CONFIG_SCHEMA = ['user' => []];

    public function testWithConfigImmutability(): void
    {
        $schemaProvider1 = $this->createSchemaProvider();
        $schemaProvider2 = $schemaProvider1->withConfig(static::READ_CONFIG);

        $this->assertNotSame(static::DEFAULT_CONFIG_SCHEMA, $schemaProvider1->read());
        $this->assertSame(static::DEFAULT_CONFIG_SCHEMA, $schemaProvider2->read());
        $this->assertNotSame($schemaProvider1, $schemaProvider2);
    }

    public function testReadFromNextProvider(): void
    {
        $provider1 = $this->createSchemaProvider();
        $provider2 = new ArraySchemaProvider(static::DEFAULT_CONFIG_SCHEMA);

        $result = $provider1->read($provider2);

        $this->assertSame(static::DEFAULT_CONFIG_SCHEMA, $result);
    }

    abstract protected function createSchemaProvider(array $config = null): SchemaProviderInterface;
}
