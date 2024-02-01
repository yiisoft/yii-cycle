<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Listener;

use Cycle\Schema\Provider\SchemaProviderInterface;
use Cycle\Schema\Provider\Support\SchemaProviderPipeline;
use PHPUnit\Framework\TestCase;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Event\AfterMigrate;
use Yiisoft\Yii\Cycle\Listener\MigrationListener;
use Yiisoft\Yii\Cycle\Tests\Unit\Listener\Stub\CallingSpyProvider;

final class MigrationListenerTest extends TestCase
{
    private function prepareSchemaProvider(array $providers = []): SchemaProviderInterface
    {
        $container = new SimpleContainer();
        return (new SchemaProviderPipeline($container))->withConfig($providers);
    }

    public function testOnEvent(): void
    {
        $provider = new CallingSpyProvider();
        $mockProvider = $this->createMock(SchemaProviderInterface::class);
        $manager = $this->prepareSchemaProvider([$provider, $mockProvider]);
        $listener = new MigrationListener($manager);
        $event = new AfterMigrate();

        $listener->onAfterMigrate($event);

        $this->assertSame(1, $provider->getClearCount());
        $this->assertSame(0, $provider->getReadCount());
        $this->assertSame(0, $provider->getWriteCount());
    }
}
