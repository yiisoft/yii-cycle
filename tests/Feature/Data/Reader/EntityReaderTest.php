<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Data\Reader;

use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\Cache\CachedCollection;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;
use Yiisoft\Yii\Cycle\Data\Reader\FilterHandler;
use Yiisoft\Yii\Cycle\Tests\Feature\Data\BaseData;

/**
 * @coversDefaultClass \Yiisoft\Yii\Cycle\Data\Reader\EntityReader
 */
final class EntityReaderTest extends BaseData
{
    /**
     * @covers ::readOne
     */
    public function testReadOne(): void
    {
        $this->fillFixtures();

        $reader = new EntityReader($this->select('user'));

        self::assertEquals(self::FIXTURES_USER[0], (array)$reader->readOne());
    }

    /**
     * @covers ::readOne
     */
    public function testReadOneFromItemsCache(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader($this->select('user')))->withLimit(3);

        $ref = (new \ReflectionProperty($reader, 'itemsCache'));
        $ref->setAccessible(true);

        self::assertFalse($ref->getValue($reader)->isCollected());
        $reader->read();

        self::assertTrue($ref->getValue($reader)->isCollected());

        self::assertEquals(self::FIXTURES_USER[0], (array)$reader->readOne());
        self::assertEquals($ref->getValue($reader)->getCollection()[0], $reader->readOne());
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIterator(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader($this->select('user')))->withLimit(1);
        self::assertEquals(self::FIXTURES_USER[0], (array) \iterator_to_array($reader->getIterator())[0]);

        $ref = (new \ReflectionProperty($reader, 'itemsCache'));
        $ref->setAccessible(true);

        $cache = new CachedCollection();
        $cache->setCollection([['foo' => 'bar']]);
        $ref->setValue($reader, $cache);

        self::assertSame(['foo' => 'bar'], (array) \iterator_to_array($reader->getIterator())[0]);
    }

    /**
     * @covers ::read
     */
    public function testRead(): void
    {
        $this->fillFixtures();

        $reader = new EntityReader($this->select('user'));

        $result = $reader->read();
        self::assertEquals(
            \array_map(static fn (array $data): \stdClass => (object) $data, self::FIXTURES_USER),
            $result,
        );
    }

    /**
     * @covers ::withSort
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
     * @covers ::getSort
     */
    public function testGetSort(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader($this->select('user')));

        self::assertNull($reader->getSort());

        $sort = Sort::only(['id'])->withOrderString('-id');
        $reader = $reader->withSort($sort);

        self::assertSame($sort, $reader->getSort());
    }

    /**
     * @covers ::count
     */
    public function testCount(): void
    {
        $this->fillFixtures();

        $reader = new EntityReader($this->select('user'));

        self::assertSame(count(self::FIXTURES_USER), $reader->count());
    }

    /**
     * @covers ::count
     * The limit option mustn't affect the count result.
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
     * @covers ::count
     */
    public function testCountWithFilter(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader($this->select('user')))->withFilter(new Equals('id', 2));

        self::assertSame(1, $reader->count());
    }

    /**
     * @covers ::withLimit
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
     * @covers ::withLimit
     */
    public function testLimitException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new EntityReader($this->select('user')))->withLimit(-1);
    }

    /**
     * @covers ::withLimit
     * @covers ::withOffset
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

    /**
     * @covers ::withFilter
     */
    public function testFilter(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader($this->select('user')))->withFilter(new Equals('id', 2));

        self::assertEquals([(object)self::FIXTURES_USER[1]], $reader->read());
    }

    /**
     * @covers ::withFilterHandlers
     */
    public function testFilterHandlers(): void
    {
        $default = [
            'and' => new FilterHandler\AllHandler(),
            'or' => new FilterHandler\AnyHandler(),
            '=' => new FilterHandler\EqualsHandler(),
            '>' => new FilterHandler\GreaterThanHandler(),
            '>=' => new FilterHandler\GreaterThanOrEqualHandler(),
            'in' => new FilterHandler\InHandler(),
            '<' => new FilterHandler\LessThanHandler(),
            '<=' => new FilterHandler\LessThanOrEqualHandler(),
            'like' => new FilterHandler\LikeHandler(),
        ];
        $custom = $this->createMock(FilterHandler\CompareHandler::class);
        $custom->method('getOperator')->willReturn('custom');

        $reader = new EntityReader($this->select('user'));
        $ref = new \ReflectionProperty(EntityReader::class, 'filterHandlers');
        $ref->setAccessible(true);

        self::assertEquals($default, $ref->getValue($reader));
        $reader = $reader->withFilterHandlers($custom);
        self::assertEquals($default + ['custom' => $custom], $ref->getValue($reader));
    }

    /**
     * @covers ::getSql
     */
    public function testGetSql(): void
    {
        $expected = 'SELECT "user"."id" AS "c0", "user"."email" AS "c1", "user"."balance" AS "c2"
            FROM "user" AS "user" LIMIT 2 OFFSET 1';

        $reader = (new EntityReader($this->select('user')))->withLimit(2)->withOffset(1);

        self::assertEquals(
            \preg_replace('/\s+/', '', $expected),
            \preg_replace('/\s+/', '', $reader->getSql())
        );
    }

    public function testMakeFilterClosureException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Filter operator "?" is not supported.');
        (new EntityReader($this->select('user')))->withFilter((new All())->withCriteriaArray([
            ['?', 'balance', '100.0'],
            ['=', 'email', 'seed@beat'],
        ]))->getSql();
    }
}
