<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\SQLite\MemoryConnectionConfig;
use Cycle\Database\Config\SQLiteDriverConfig;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\Mapper\Mapper;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select\Repository;
use Cycle\ORM\Select\Source;
use Cycle\Schema\Generator\ForeignKeys;
use Cycle\Schema\Generator\GenerateModifiers;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\RenderModifiers;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\GeneratorInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Schema\Conveyor\AttributedSchemaConveyor;
use Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;
use Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider\Stub\FakePost;
use Yiisoft\Yii\Cycle\Tests\Feature\Schema\Stub\ArraySchemaProvider;

final class FromConveyorSchemaProviderTest extends BaseSchemaProvider
{
    private SimpleContainer $container;
    private DatabaseManager $dbal;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer([
            Aliases::class => new Aliases(),
            ResetTables::class => new ResetTables(),
            GenerateRelations::class => new GenerateRelations(),
            GenerateModifiers::class => new GenerateModifiers(),
            ValidateEntities::class => new ValidateEntities(),
            RenderTables::class => new RenderTables(),
            RenderRelations::class => new RenderRelations(),
            RenderModifiers::class => new RenderModifiers(),
            ForeignKeys::class => new ForeignKeys(),
            GenerateTypecast::class => new GenerateTypecast(),
        ]);

        $this->dbal = new DatabaseManager(new DatabaseConfig([
            'default' => 'default',
            'databases' => ['default' => ['connection' => 'sqlite']],
            'connections' => [
                'sqlite' => new SQLiteDriverConfig(connection: new MemoryConnectionConfig()),
            ],
        ]));
    }

    public function testWithEmptyConfig(): void
    {
        $conveyor = $this->createMock(SchemaConveyorInterface::class);
        $conveyor->expects($this->once())->method('getGenerators')->willReturn([]);
        $conveyor->expects($this->never())->method('addGenerator');

        $provider = $this->createSchemaProvider(conveyor: $conveyor);

        $provider->withConfig([]);

        $this->assertSame([], $provider->read());
    }

    public function testWithConfig(): void
    {
        $provider = $this->createSchemaProvider();
        $generator = $this->createMock(GeneratorInterface::class);

        $provider = $provider->withConfig(['generators' => [$generator]]);

        $ref = new \ReflectionProperty($provider, 'generators');
        $ref->setAccessible(true);

        $this->assertSame([$generator], $ref->getValue($provider));
    }

    public function testReadFromConveyor(): void
    {
        $attributed = new AttributedSchemaConveyor($this->container);
        $attributed->addEntityPaths([__DIR__ . '/Stub']);

        $provider = $this->createSchemaProvider(conveyor: $attributed, dbal: $this->dbal);

        $this->assertEquals([
            'fakePost' => [
                SchemaInterface::ENTITY => FakePost::class,
                SchemaInterface::MAPPER => Mapper::class,
                SchemaInterface::SOURCE => Source::class,
                SchemaInterface::REPOSITORY => Repository::class,
                SchemaInterface::DATABASE => 'default',
                SchemaInterface::TABLE => 'fake_post',
                SchemaInterface::PRIMARY_KEY => ['id'],
                SchemaInterface::FIND_BY_KEYS => ['id'],
                SchemaInterface::COLUMNS => ['id' => 'id', 'title' => 'title', 'createdAt' => 'created_at'],
                SchemaInterface::RELATIONS => [],
                SchemaInterface::SCOPE => null,
                SchemaInterface::TYPECAST => ['id' => 'int', 'createdAt' => 'datetime'],
                SchemaInterface::SCHEMA => [],
                SchemaInterface::TYPECAST_HANDLER => null,
            ],
        ], $provider->read());
    }

    public function testReadFromConveyorAndNextProvider(): void
    {
        if (!is_dir(__DIR__ . '/Stub/Foo')) {
            mkdir(__DIR__ . '/Stub/Foo', 0777, true);
        }

        $attributed = new AttributedSchemaConveyor($this->container);
        $attributed->addEntityPaths([__DIR__ . '/Stub/Foo']);

        $provider = $this->createSchemaProvider(conveyor: $attributed, dbal: $this->dbal);

        $this->assertEquals(
            self::READ_CONFIG_SCHEMA,
            $provider->read(new ArraySchemaProvider(self::READ_CONFIG_SCHEMA))
        );

        if (is_dir(__DIR__ . '/Stub/Foo')) {
            rmdir(__DIR__ . '/Stub/Foo');
        }
    }

    protected function createSchemaProvider(
        array $config = null,
        SchemaConveyorInterface $conveyor = null,
        DatabaseProviderInterface $dbal = null
    ): SchemaProviderInterface {
        $provider = new FromConveyorSchemaProvider(
            $conveyor ?? $this->createMock(SchemaConveyorInterface::class),
            $dbal ?? $this->createMock(DatabaseProviderInterface::class),
        );

        return $config === null ? $provider : $provider->withConfig($config);
    }

    public function testWithConfigImmutability(): void
    {
        $schemaProvider1 = $this->createSchemaProvider();
        $schemaProvider2 = $schemaProvider1->withConfig(self::READ_CONFIG);

        $this->assertNotSame($schemaProvider1, $schemaProvider2);
    }
}
