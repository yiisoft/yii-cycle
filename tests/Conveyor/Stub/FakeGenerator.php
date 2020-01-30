<?php

namespace Yiisoft\Yii\Cycle\Tests\Conveyor\Stub;

use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;

class FakeGenerator implements GeneratorInterface
{
    private string $originClass;

    public function __construct(string $originClass)
    {
        $this->originClass = $originClass;
    }

    public function run(Registry $registry): Registry
    {
    }

    public function originClass(): string
    {
        return $this->originClass;
    }
}
