<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Data\Reader\FilterHandler;

use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;
use Yiisoft\Yii\Cycle\Tests\Feature\Data\BaseData;

final class GreaterThanHandlerTest extends BaseData
{
    public function testGreaterThanHandler(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader($this->select('user')))->withFilter(new GreaterThan('balance', 499));

        $this->assertEquals([(object)self::FIXTURES_USER[3]], $reader->read());
    }
}
