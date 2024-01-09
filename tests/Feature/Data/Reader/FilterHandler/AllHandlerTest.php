<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Data\Reader\FilterHandler;

use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;
use Yiisoft\Yii\Cycle\Tests\Feature\Data\BaseData;

final class AllHandlerTest extends BaseData
{
    public function testAllHandler(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader($this->select('user')))->withFilter((new All())->withCriteriaArray([
            ['=', 'balance', '100.0'],
            ['=', 'email', 'seed@beat'],
        ]));

        $this->assertEquals([(object)self::FIXTURES_USER[2]], $reader->read());
    }
}
