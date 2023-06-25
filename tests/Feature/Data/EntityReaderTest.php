<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Data;

use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

/**
 * @covers \Yiisoft\Yii\Cycle\Data\Reader\EntityReader
 */
final class EntityReaderTest extends BaseData
{
    /**
     * Test {@see EntityReader::readOne()}
     */
    public function testReadOne(): void
    {
        $this->fillFixtures();

        $reader = new EntityReader(
            $this->select('user'),
        );

        self::assertEquals(self::FIXTURES_USER[0], (array)$reader->readOne());
    }

    /**
     * Test {@see EntityReader::read()}
     */
    public function testRead(): void
    {
        $this->fillFixtures();

        $reader = new EntityReader(
            $this->select('user'),
        );

        $result = $reader->read();
        self::assertEquals(
            \array_map(static fn (array $data): \stdClass => (object) $data, self::FIXTURES_USER),
            $result,
        );
    }

    /**
     * Test {@see EntityReader::withSort()}
     */
    public function testWithSort(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader(
            $this->select('user'),
        ))
            // Reverse order
            ->withSort(Sort::only(['id'])->withOrderString('-id'));

        $result = $reader->read();
        self::assertEquals(
            \array_map(static fn (array $data): object => (object) $data, \array_reverse(self::FIXTURES_USER)),
            $result,
        );
        self::assertSame('-id', $reader->getSort()->getOrderAsString());
    }

    /**
     * Test {@see EntityReader::count()}
     */
    public function testCount(): void
    {
        $this->fillFixtures();

        $reader = new EntityReader(
            $this->select('user'),
        );

        self::assertSame(count(self::FIXTURES_USER), $reader->count());
    }

    /**
     * Test {@see EntityReader::count()} with limit. The limit option mustn't affect the count result.
     */
    public function testCountWithLimit(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader(
            $this->select('user'),
        ))->withLimit(1);

        self::assertSame(count(self::FIXTURES_USER), $reader->count());
    }

    /**
     * Test {@see EntityReader::withLimit()}
     */
    public function testLimit(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader(
            $this->select('user'),
        ))
            ->withLimit(2);

        self::assertEquals(
            [(object)self::FIXTURES_USER[0], (object)self::FIXTURES_USER[1]],
            $reader->read(),
        );
    }

    /**
     * Test {@see EntityReader::withLimit()} and {@see EntityReader::withOffset()}
     */
    public function testLimitOffset(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader(
            $this->select('user'),
        ))
            ->withLimit(2)->withOffset(1);

        self::assertEquals(
            [(object)self::FIXTURES_USER[1], (object)self::FIXTURES_USER[2]],
            $reader->read(),
        );
    }
}
