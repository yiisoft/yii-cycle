<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Factory\DbalFactory;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Test\Support\Container\SimpleContainer;

abstract class BaseDbalFactoryTest extends TestCase
{
    protected const ALIASES = [
        '@test' => 'test',
    ];

    protected ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer([
            NullLogger::class => new NullLogger(),
            Aliases::class => new Aliases(self::ALIASES),
        ]);
    }

}
