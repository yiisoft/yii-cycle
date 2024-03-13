<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Cycle\Exception\ConfigException;

final class ConfigExceptionTest extends TestCase
{
    public function testGetCode(): void
    {
        $exception = new ConfigException(['item1', 'item2'], 'test');
        $this->assertSame(0, $exception->getCode());
    }
}
