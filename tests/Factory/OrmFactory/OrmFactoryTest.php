<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Factory\OrmFactory;

use Cycle\ORM\FactoryInterface;

final class OrmFactoryTest extends BaseOrmFactoryTest
{
    /**
     * Factory for ORM Factory just works
     */
    public function testCreate(): void
    {
        $factory = $this->makeFactory();

        $this->assertInstanceOf(FactoryInterface::class, $factory);
    }
}
