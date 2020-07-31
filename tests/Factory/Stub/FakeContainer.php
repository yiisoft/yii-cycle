<?php

namespace Yiisoft\Yii\Cycle\Tests\Factory\Stub;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FakeContainer implements ContainerInterface
{

    private TestCase $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function get($id)
    {
        return $this->testCase->getMockBuilder($id)->getMock();
    }

    public function has($id)
    {
        return true;
    }
}
