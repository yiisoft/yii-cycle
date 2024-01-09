<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Factory;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\Select;
use PHPUnit\Framework\TestCase;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Exception\NotFoundException;
use Yiisoft\Yii\Cycle\Exception\NotInstantiableClassException;
use Yiisoft\Yii\Cycle\Factory\RepositoryContainer;
use Yiisoft\Yii\Cycle\Tests\Unit\Stub\FakeEntity;
use Yiisoft\Yii\Cycle\Tests\Unit\Stub\FakeRepository;

final class RepositoryContainerTest extends TestCase
{
    private Schema $schema;

    protected function setUp(): void
    {
        $this->schema = new Schema([
            'foo' => [
                Schema::ENTITY => FakeEntity::class,
                Schema::REPOSITORY => FakeRepository::class,
            ]
        ]);
    }

    public function testGetNotFoundException(): void
    {
        $orm = $this->createMock(ORMInterface::class);
        $orm->expects($this->once())->method('getSchema')->willReturn(new Schema([]));

        $container = new RepositoryContainer(new SimpleContainer([ORMInterface::class => $orm]));

        $this->expectException(NotFoundException::class);
        $container->get(FakeRepository::class);
    }

    public function testGetNotInstantiableClassException(): void
    {
        $container = new RepositoryContainer(new SimpleContainer([
            ORMInterface::class => $this->createMock(ORMInterface::class)
        ]));

        $this->expectException(NotInstantiableClassException::class);
        $container->get('foo');
    }

    public function testMakeAndGet(): void
    {
        $orm = $this->createMock(ORMInterface::class);
        $orm->expects($this->once())->method('getSchema')->willReturn($this->schema);
        $orm->expects($this->once())
            ->method('getRepository')
            ->with('foo')
            ->willReturn(new FakeRepository($this->createMock(Select::class)));

        $container = new RepositoryContainer(new SimpleContainer([ORMInterface::class => $orm]));
        $instancesRef = new \ReflectionProperty($container, 'instances');
        $instancesRef->setAccessible(true);

        $this->assertSame([], $instancesRef->getValue($container));
        $this->assertInstanceOf(FakeRepository::class, $container->get(FakeRepository::class));
    }

    public function testGetFromInstances(): void
    {
        $orm = $this->createMock(ORMInterface::class);
        $orm->expects($this->once())->method('getSchema')->willReturn($this->schema);
        $orm->expects($this->once())
            ->method('getRepository')
            ->with('foo')
            ->willReturn(new FakeRepository($this->createMock(Select::class)));

        $container = new RepositoryContainer(new SimpleContainer([ORMInterface::class => $orm]));
        $instancesRef = new \ReflectionProperty($container, 'instances');
        $instancesRef->setAccessible(true);

        $this->assertSame([], $instancesRef->getValue($container));
        $repository = $container->get(FakeRepository::class);
        $this->assertInstanceOf(FakeRepository::class, $repository);

        $this->assertSame([FakeRepository::class => $repository], $instancesRef->getValue($container));
        $this->assertSame($repository, $container->get(FakeRepository::class));
    }

    public function testHasNonInterface(): void
    {
        $orm = $this->createMock(ORMInterface::class);
        $orm->expects($this->never())->method('getSchema');

        $container = new RepositoryContainer(new SimpleContainer([ORMInterface::class => $orm]));

        $this->assertFalse($container->has('foo'));
    }

    public function testHas(): void
    {
        $orm = $this->createMock(ORMInterface::class);
        $orm->expects($this->once())->method('getSchema')->willReturn($this->schema);

        $container = new RepositoryContainer(new SimpleContainer([ORMInterface::class => $orm]));

        $repository = $this->createMock(RepositoryInterface::class);
        $this->assertTrue($container->has(FakeRepository::class));
        $this->assertFalse($container->has($repository::class));
    }
}
