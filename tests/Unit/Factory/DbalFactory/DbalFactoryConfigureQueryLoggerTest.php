<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Factory\DbalFactory;

use Psr\Log\LoggerInterface;
use Yiisoft\Test\Support\Log\SimpleLogger;
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
        $factory = (new DbalFactory($logger))->create([
            'query-logging' => true,
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
        ]);
        return $factory->driver('fake')->getLogger();
    }

    public function testLoggerDefinition(): void
    {
        $simpleLogger = $this->container->get(SimpleLogger::class);

        $dbalLogger = $this->prepareLoggerFromDbalFactory($simpleLogger);

        $this->assertSame($dbalLogger, $simpleLogger);
    }
}
