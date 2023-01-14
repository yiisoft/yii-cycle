<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Factory\OrmFactory;

use Cycle\ORM\Collection\CollectionFactoryInterface;
use Cycle\ORM\FactoryInterface;
use Yiisoft\Yii\Cycle\Tests\Factory\OrmFactory\Stub\CustomArrayCollectionFactory;

final class OrmFactoryTest extends BaseOrmFactoryTest
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
}
