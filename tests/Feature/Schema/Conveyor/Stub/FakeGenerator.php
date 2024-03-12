<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Conveyor\Stub;

use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;

class FakeGenerator implements GeneratorInterface
{
    public function __construct(private string $originClass)
    {
    }

    public function run(Registry $registry): Registry
    {
    }

    public function originClass(): string
    {
        return $this->originClass;
    }
}
