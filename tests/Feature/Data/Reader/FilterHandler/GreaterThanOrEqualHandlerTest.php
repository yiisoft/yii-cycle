<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Data\Reader\FilterHandler;

use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;
use Yiisoft\Yii\Cycle\Tests\Feature\Data\BaseData;

final class GreaterThanOrEqualHandlerTest extends BaseData
{
    public function testGreaterThanOrEqualHandler(): void
    {
        $this->fillFixtures();

        $reader = (new EntityReader($this->select('user')))
            ->withFilter(new GreaterThanOrEqual('balance', 500));

        $this->assertEquals([(object)self::FIXTURES_USER[3]], $reader->read());
    }
}
