<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Factory;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Tests\Factory\Stub\FakeContainer;

class DbalFactoryPrepareLoggerTest extends TestCase
{

    private $factory;
    private $method;

    protected function setUp(): void
    {
        $this->factory = new DbalFactory([]);

        $containerProperty = (new ReflectionClass(DbalFactory::class))->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($this->factory, new FakeContainer($this));

        $this->method = (new ReflectionClass(DbalFactory::class))->getMethod('prepareLogger');
        $this->method->setAccessible(true);
    }

    protected function prepareLogger($logger)
    {
        return $this->method->invoke($this->factory, $logger);
    }

    public function testString(): void
    {
        $this->assertInstanceOf(NullLogger::class, $this->prepareLogger(NullLogger::class));
    }

    public function testClosure(): void
    {
        $this->assertInstanceOf(NullLogger::class, $this->prepareLogger(function () {
            return new NullLogger();
        }));
    }

    public function testLoggerInterface(): void
    {
        $this->assertInstanceOf(NullLogger::class, $this->prepareLogger(NullLogger::class));
    }

    public function testInvalid(): void
    {
        $this->expectExceptionMessage('Invalid logger.');
        $this->prepareLogger(null);
    }
}
