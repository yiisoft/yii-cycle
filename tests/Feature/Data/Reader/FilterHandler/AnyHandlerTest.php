<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Data\Reader\FilterHandler;

use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;
use Yiisoft\Yii\Cycle\Tests\Feature\Data\BaseData;

final class AnyHandlerTest extends BaseData
{
    public function testAnyHandler(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader($this->select('user')))->withFilter((new Any())->withCriteriaArray([
            ['=', 'id', 2],
            ['=', 'id', 3],
        ]));

        $this->assertEquals([(object)self::FIXTURES_USER[1], (object)self::FIXTURES_USER[2]], $reader->read());
    }
}
