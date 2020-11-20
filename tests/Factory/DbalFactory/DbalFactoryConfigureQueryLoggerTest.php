<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Factory\DbalFactory;

use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use stdClass;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Tests\Factory\DbalFactory\Stub\FakeDriver;

final class DbalFactoryConfigureQueryLoggerTest extends BaseDbalFactoryTest
{
    /**
     * @param LoggerInterface|string $logger Classname or object
     *
     * @return LoggerInterface|null
     */
    protected function prepareLoggerFromDbalFactory($logger): ?LoggerInterface
    {
        $factory = (new DbalFactory([
            'query-logger' => $logger,
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'fake'],
            ],
            'connections' => [
                'fake' => [
                    'driver' => FakeDriver::class,
                    'connection' => 'fake',
                    'username' => '',
                    'password' => '',
                ],
            ],
        ]))($this->container);
        return $factory->driver('fake')->getLogger();
    }

    public function testLoggerDefinitionAsStringDefinition(): void
    {
        $nullLogger = $this->container->get(NullLogger::class);

        $dbalLogger = $this->prepareLoggerFromDbalFactory(NullLogger::class);

        // Logger was got from the container
        $this->assertSame($nullLogger, $dbalLogger);
    }

    public function testLoggerDefinitionAsObject(): void
    {
        $nullLogger = $this->container->get(NullLogger::class);

        $dbalLogger = $this->prepareLoggerFromDbalFactory($nullLogger);

        $this->assertSame($dbalLogger, $nullLogger);
    }

    public function testLoggerDefinitionAsInvalidDefinition(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);

        $this->prepareLoggerFromDbalFactory('Invalid definition');
    }

    public function testLoggerDefinitionAsInvalidClassName(): void
    {
        $this->expectException(RuntimeException::class);

        $this->prepareLoggerFromDbalFactory(Aliases::class);
    }

    public function testLoggerDefinitionAsInvalidObject(): void
    {
        $this->expectException(RuntimeException::class);

        $this->prepareLoggerFromDbalFactory(new stdClass());
    }
}
