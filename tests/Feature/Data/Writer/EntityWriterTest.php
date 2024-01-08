<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Data\Writer;

use Cycle\ORM\EntityManagerInterface;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;
use Yiisoft\Yii\Cycle\Data\Writer\EntityWriter;
use Yiisoft\Yii\Cycle\Tests\Feature\Data\BaseData;

/**
 * @covers \Yiisoft\Yii\Cycle\Data\Writer\EntityWriter
 */
final class EntityWriterTest extends BaseData
{
    public function testWrite(): void
    {
        $this->fillFixtures();
        $orm = $this->getOrm();

        $writer = new EntityWriter(
            $this->container->get(EntityManagerInterface::class)
        );
        $writer->write($users = [
            $orm->make('user', ['id' => 99998, 'email' => 'super@test1.com', 'balance' => 1000.0]),
            $orm->make('user', ['id' => 99999, 'email' => 'super@test2.com', 'balance' => 999.0]),
        ]);

        $reader = new EntityReader(
            $this->select('user')->where('id', 'in', [99998, 99999]),
        );

        self::assertEquals($users, $reader->read());
    }

    public function testDelete(): void
    {
        $this->fillFixtures();
        $orm = $this->getOrm();

        $writer = new EntityWriter($this->container->get(EntityManagerInterface::class));
        $reader = new EntityReader($this->select('user')->where('id', 'in', [1, 2, 3]));
        // Iterator doesn't use cache
        $entities = \iterator_to_array($reader->getIterator());

        $writer->delete($entities);

        self::assertCount(3, $entities);
        self::assertEquals([], \iterator_to_array($reader->getIterator()));
    }
}
