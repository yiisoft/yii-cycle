<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider\Support;

use Yiisoft\Yii\Cycle\Schema\Provider\Support\SchemaProviderPipeline;
use Yiisoft\Yii\Cycle\Tests\Feature\Schema\Stub\ArraySchemaProvider;

final class SchemaProviderPipelineTest extends BaseProviderCollector
{
    protected const READ_CONFIG = [
        ArraySchemaProvider::class => self::READ_CONFIG_SCHEMA,
    ];

    protected function createSchemaProvider(?array $config = []): SchemaProviderPipeline
    {
        $provider = new SchemaProviderPipeline($this->container);
        return $config === null ? $provider : $provider->withConfig($config);
    }

    // Reading test

    public function testShortCircuitInstantiation(): void
    {
        $this->prepareContainer([
            'goodProvider' => new ArraySchemaProvider(self::READ_CONFIG_SCHEMA),
            'badProvider' => 'not an object',
        ]);

        $provider = $this->createSchemaProvider(['goodProvider', 'badProvider', 'undefined provider']);

        $this->assertSame(self::READ_CONFIG_SCHEMA, $provider->read());
    }
}
