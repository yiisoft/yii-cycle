<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Factory\DbalFactory;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;
use Spiral\Core\FactoryInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Factory\CycleDynamicFactory;

abstract class BaseDbalFactoryTest extends TestCase
{
    protected const ALIASES = [
        '@test' => 'test',
    ];

    protected ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer(
            [
                NullLogger::class => new NullLogger(),
                Aliases::class => new Aliases(self::ALIASES),
            ],
            function (string $id) {
                if ($id === FactoryInterface::class) {
                    return new CycleDynamicFactory($this->container);
                }
                throw new NotFoundException($id);
            }
        );
    }
}
