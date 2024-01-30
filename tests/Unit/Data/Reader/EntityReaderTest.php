<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Data\Reader;

use Cycle\Database\Query\SelectQuery;
use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

final class EntityReaderTest extends TestCase
{
    public function testNormalizeSortingCriteria(): void
    {
        $reader = new EntityReader($this->createMock(SelectQuery::class));

        $ref = new \ReflectionMethod($reader, 'normalizeSortingCriteria');
        $ref->setAccessible(true);

        $this->assertSame(
            ['id' => 'ASC', 'name' => 'DESC', 'email' => 'ASC'],
            $ref->invoke($reader, ['id' => 'ASC', 'name' => SORT_DESC, 'email' => SORT_ASC])
        );
    }

    public function testOffset(): void
    {
        $select = $this->createMock(SelectQuery::class);
        $select->expects($this->once())->method('offset')->with(10)->willReturnSelf();

        $reader = new EntityReader($select);
        $reader->withOffset(10)->getSql();
    }

    public function testOrderBy(): void
    {
        $select = $this->createMock(SelectQuery::class);
        $select->expects($this->once())->method('orderBy')->with(['id' => 'DESC'])->willReturnSelf();

        $reader = new EntityReader($select);
        $reader->withSort(Sort::only(['id'])->withOrderString('-id'))->getSql();
    }

    public function testLimit(): void
    {
        $select = $this->createMock(SelectQuery::class);
        $select->expects($this->once())->method('limit')->with(5)->willReturnSelf();

        $reader = new EntityReader($select);
        $reader->withLimit(5)->getSql();
    }
}
