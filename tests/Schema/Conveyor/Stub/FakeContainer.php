<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Conveyor\Stub;

use Cycle\Schema\GeneratorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;

class FakeContainer implements ContainerInterface
{
    private TestCase $testCase;
    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }
    public function get($id)
    {
        if ($id === Aliases::class) {
            return new Aliases(['@test-dir' => __DIR__]);
        }
        if (is_a($id, GeneratorInterface::class, true)) {
            return new FakeGenerator($id);
        }
        return $this->testCase->getMockBuilder($id)->getMock();
    }
    public function has($id)
    {
        return true;
    }
}
