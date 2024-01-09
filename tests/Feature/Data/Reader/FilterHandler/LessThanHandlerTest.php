<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Data\Reader\FilterHandler;

use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;
use Yiisoft\Yii\Cycle\Tests\Feature\Data\BaseData;

final class LessThanHandlerTest extends BaseData
{
    public function testLessThanHandler(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader($this->select('user')))->withFilter(new LessThan('balance', 1.1));

        $this->assertEquals([(object)self::FIXTURES_USER[1]], $reader->read());
    }
}
