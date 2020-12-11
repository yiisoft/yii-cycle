<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider\Support;

use Cycle\ORM\Schema;
use Psr\Container\ContainerInterface;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Exception\DuplicateRoleException;
use Yiisoft\Yii\Cycle\Schema\Provider\Support\MergeSchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Provider\BaseSchemaProviderTest;
use Yiisoft\Yii\Cycle\Tests\Schema\Stub\ArraySchemaProvider;

final class MergeSchemaProviderTest extends BaseSchemaProviderTest
{
    protected const READ_CONFIG = [
        [ArraySchemaProvider::class, self::SCHEMA_PART_1],
        [ArraySchemaProvider::class, self::SCHEMA_PART_2],
    ];
    protected const READ_CONFIG_SCHEMA = self::SCHEMA_PART_1 + self::SCHEMA_PART_2;
    protected const SCHEMA_PART_1 = parent::READ_CONFIG_SCHEMA;
    protected const SCHEMA_PART_2 = [
        'post' => [
            Schema::ENTITY => \stdClass::class,
            Schema::MAPPER => \stdClass::class,
        ],
    ];
    protected const SCHEMA_CONFLICT = [
        'post' => [
            Schema::ENTITY => \stdClass::class,
            Schema::DATABASE => 'default',
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

    protected function createSchemaProvider(?array $config = []): MergeSchemaProvider
    {
        $provider = new MergeSchemaProvider($this->container);
        return $config === null ? $provider : $provider->withConfig($config);
    }

    public function testMergeSameValueConflict(): void
    {
        $provider = $this->createSchemaProvider([...self::READ_CONFIG, new ArraySchemaProvider(self::SCHEMA_PART_1)]);
        $schema = $provider->read();
        self::assertSame(self::READ_CONFIG_SCHEMA, $schema);
    }

    public function testMergeDifferentValueConflict(): void
    {
        $this->expectException(DuplicateRoleException::class);

        $provider = $this->createSchemaProvider([...self::READ_CONFIG, new ArraySchemaProvider(self::SCHEMA_CONFLICT)]);
        $provider->read();
    }
}
