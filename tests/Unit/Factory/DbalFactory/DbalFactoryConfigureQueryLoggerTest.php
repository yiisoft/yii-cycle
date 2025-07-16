<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Factory\DbalFactory;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Yiisoft\Yii\Cycle\Factory\DbalFactory;
use Yiisoft\Yii\Cycle\Tests\Unit\Stub\FakeConnectionConfig;
use Yiisoft\Yii\Cycle\Tests\Unit\Stub\FakeDriver;
use Yiisoft\Yii\Cycle\Tests\Unit\Stub\FakeDriverConfig;

final class DbalFactoryConfigureQueryLoggerTest extends BaseDbalFactory
{
    /**
     * @param LoggerInterface|null $logger Classname or object
     *
     * @return LoggerInterface|null
     */
    protected function prepareLoggerFromDbalFactory(?LoggerInterface $logger): ?LoggerInterface
    {
        $factory = (new DbalFactory([
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'fake'],
            ],
            'connections' => [
                'fake' => new FakeDriverConfig(
                    connection: new FakeConnectionConfig(),
                    driver: FakeDriver::class,
                ),
            ],
        ], $logger))->create();
        return $factory->driver('fake')->getLogger();
    }

    public function testLoggerDefinition(): void
    {
        $nullLogger = $this->container->get(NullLogger::class);

        $dbalLogger = $this->prepareLoggerFromDbalFactory($nullLogger);

        $this->assertSame($dbalLogger, $nullLogger);
    }
}
