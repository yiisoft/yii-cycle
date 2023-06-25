<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider;

use Cycle\ORM\SchemaInterface as Schema;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;
use Yiisoft\Yii\Cycle\Tests\Feature\Schema\Stub\ArraySchemaProvider;

abstract class BaseSchemaProvider extends TestCase
{
    protected const READ_CONFIG = [];
    protected const READ_CONFIG_SCHEMA = [
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
    protected const DEFAULT_SCHEMA = null;

    public function testWithConfigImmutability(): void
    {
        $schemaProvider1 = $this->createSchemaProvider();
        $schemaProvider2 = $schemaProvider1->withConfig(static::READ_CONFIG);

        $this->assertSame(static::DEFAULT_SCHEMA, $schemaProvider1->read());
        $this->assertSame(static::READ_CONFIG_SCHEMA, $schemaProvider2->read());
        $this->assertNotSame($schemaProvider1, $schemaProvider2);
    }

    public function testReadFromNextProvider(): void
    {
        $provider1 = $this->createSchemaProvider();
        $provider2 = new ArraySchemaProvider(static::READ_CONFIG_SCHEMA);

        $result = $provider1->read($provider2);

        $this->assertSame(static::READ_CONFIG_SCHEMA, $result);
    }

    abstract protected function createSchemaProvider(array $config = null): SchemaProviderInterface;
}
