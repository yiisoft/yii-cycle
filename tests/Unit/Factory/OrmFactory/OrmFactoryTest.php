<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Factory\OrmFactory;

use Cycle\ORM\Collection\CollectionFactoryInterface;
use Cycle\ORM\FactoryInterface;
use stdClass;
use Yiisoft\Yii\Cycle\Tests\Unit\Factory\OrmFactory\Stub\CustomArrayCollectionFactory;

final class OrmFactoryTest extends BaseOrmFactory
{
    /**
     * Factory for ORM Factory just works
     */
    public function testCreate(): void
    {
        $factory = $this->makeFactory();
        $defaultCollectionFactory = $factory->collection();

        $this->assertInstanceOf(FactoryInterface::class, $factory);
        $this->assertInstanceOf(CollectionFactoryInterface::class, $defaultCollectionFactory);
    }

    public function testDefineCustomCollectionFactory(): void
    {
        $factory = $this->makeFactory([
            'factories' => [
                'custom' => CustomArrayCollectionFactory::class,
            ],
        ]);
        $defaultCollectionFactory = $factory->collection();
        $customCollectionFactory = $factory->collection('custom');

        $this->assertNotInstanceOf(CustomArrayCollectionFactory::class, $defaultCollectionFactory);
        $this->assertInstanceOf(CustomArrayCollectionFactory::class, $customCollectionFactory);
    }

    public function testDefineCustomCollectionFactoryAsDefault(): void
    {
        $factory = $this->makeFactory([
            'default' => 'custom',
            'factories' => [
                'custom' => CustomArrayCollectionFactory::class,
            ],
        ]);
        $defaultCollectionFactory = $factory->collection();
        $customCollectionFactory = $factory->collection('custom');

        $this->assertInstanceOf(CustomArrayCollectionFactory::class, $defaultCollectionFactory);
        $this->assertInstanceOf(CustomArrayCollectionFactory::class, $customCollectionFactory);
    }

    public function testDefineCustomCollectionFactoryAsDefaultOnly(): void
    {
        $factory = $this->makeFactory([
            'default' => CustomArrayCollectionFactory::class,
        ]);
        $defaultCollectionFactory = $factory->collection();

        $this->assertInstanceOf(CustomArrayCollectionFactory::class, $defaultCollectionFactory);
    }

    public function testDefineWrongCollectionFactory(): void
    {
        $this->expectException(\Yiisoft\Yii\Cycle\Exception\ConfigException::class);
        $this->expectExceptionMessage(
            '(yiisoft/yii-cycle -> collections -> factories): Collection factory `custom` should be instance of '
            . 'Cycle\ORM\Collection\CollectionFactoryInterface or its declaration. '
            . 'Instance of stdClass was received instead.',
        );

        $this->makeFactory([
            'factories' => [
                'custom' => stdClass::class,
            ],
        ]);
    }

    public function testDefineWrongDefaultCollectionFactory(): void
    {
        $this->expectException(\Yiisoft\Yii\Cycle\Exception\ConfigException::class);
        $this->expectExceptionMessage(
            '(yiisoft/yii-cycle -> collections -> default): Default collection factory `wrong` not found.',
        );

        $this->makeFactory([
            'default' => 'wrong',
            'factories' => [
                'custom' => CustomArrayCollectionFactory::class,
            ],
        ]);
    }
}
