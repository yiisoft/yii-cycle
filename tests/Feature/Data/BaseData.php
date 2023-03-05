<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Data;

use Cycle\Database\Database;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\EntityManager;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Mapper\StdMapper;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select;
use PHPUnit\Framework\TestCase;
use Spiral\Core\FactoryInterface;
use Yiisoft\Injector\Injector;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Factory\CycleDynamicFactory;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;

class BaseData extends TestCase
{
    protected const FIXTURES_USER = [
        ['id' => 1, 'email' => 'foo@bar', 'balance' => '10.25'],
        ['id' => 2, 'email' => 'bar@foo', 'balance' => '1.0'],
        ['id' => 3, 'email' => 'seed@beat', 'balance' => '100.0'],
        ['id' => 4, 'email' => 'the@best', 'balance' => '500.0'],
        ['id' => 5, 'email' => 'test@test', 'balance' => '42.0'],
    ];

    protected ?SimpleContainer $container = null;
    // cache
    private ?ORMInterface $orm = null;
    private ?DatabaseProviderInterface $dbal = null;

    public function testDefinitions(): void
    {
        self::assertInstanceOf(Injector::class, $this->container->get(Injector::class));
        self::assertInstanceOf(FactoryInterface::class, $this->container->get(FactoryInterface::class));
        self::assertInstanceOf(ORMInterface::class, $this->container->get(ORM::class));
    }

    protected function setUp(): void
    {
        $this->prepareContainer();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->orm = null;
        $this->dbal = null;
        $this->container = null;
    }

    protected function fillFixtures(): void
    {
        /** @var Database $db */
        $db = $this->container->get(DatabaseProviderInterface::class)->database();

        $user = $db->table('user')->getSchema();
        $user->column('id')->bigInteger()->primary(true);
        $user->column('email')->string(255)->nullable(false);
        $user->column('balance')->float()->nullable(false)->defaultValue(0.0);
        $user->save();

        $db->insert('user')
            ->columns(['id', 'email', 'balance'])
            ->values(static::FIXTURES_USER)
            ->run();
    }

    protected function dbalConfig(): array
    {
        return [
            // SQL query logger. Definition of Psr\Log\LoggerInterface
            'query-logger' => null,
            // Default database
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'sqlite'],
            ],
            'connections' => [
                'sqlite' => new \Cycle\Database\Config\SQLiteDriverConfig(
                    connection: new \Cycle\Database\Config\SQLite\MemoryConnectionConfig()
                ),
            ],
        ];
    }

    protected function select(string $role): Select
    {
        return new Select($this->getOrm(), $role);
    }

    protected function getOrm(): ORMInterface
    {
        return $this->container->get(ORMInterface::class);
    }

    private function prepareContainer(): void
    {
        $this->container = new SimpleContainer([
            FactoryInterface::class => &$factory,
            CycleDynamicFactory::class => &$factory,
            Injector::class => &$injector,
            SchemaInterface::class => $this->createSchema(),
        ], fn (string $id) => match ($id) {
            DatabaseProviderInterface::class, DatabaseManager::class =>
                $this->dbal ??= (new DbalFactory($this->dbalConfig()))($this->container),
            ORMInterface::class, ORM::class, => $this->orm ??= $this->createOrm(),
            EntityManagerInterface::class, EntityManager::class =>
                new EntityManager($this->container->get(ORMInterface::class)),
            default => throw new \RuntimeException("Unknown service ID: $id"),
        });

        $injector = new Injector($this->container);
        $factory = new CycleDynamicFactory($injector);
    }

    private function createOrm(): ORMInterface
    {
        $this->container->get(DatabaseProviderInterface::class);
        $factory = (new OrmFactory([]))(
            $this->container->get(DatabaseProviderInterface::class),
            $this->container->get(FactoryInterface::class),
            $this->container->get(Injector::class),
        );

        return new ORM(
            $factory,
            $this->container->get(SchemaInterface::class),
        );
    }

    /**
     * Cycle ORM Schema
     */
    private function createSchema(): SchemaInterface
    {
        return new Schema([
            'user' => [
                SchemaInterface::MAPPER => StdMapper::class,
                SchemaInterface::DATABASE => 'default',
                SchemaInterface::TABLE => 'user',
                SchemaInterface::PRIMARY_KEY => 'id',
                SchemaInterface::COLUMNS => [
                    // property => column
                    'id' => 'id',
                    'email' => 'email',
                    'balance' => 'balance',
                ],
                SchemaInterface::TYPECAST => [
                    'id' => 'int',
                    'balance' => 'float',
                ],
                SchemaInterface::RELATIONS => [],
            ],
        ]);
    }
}
