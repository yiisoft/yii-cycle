<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider\Support;

use RuntimeException;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

/**
 * A class for working with a group of schema providers.
 * When the schema is read, it queues the specified schema providers using the {@see DeferredSchemaProviderDecorator}.
 */
final class SchemaProviderPipeline extends BaseProviderCollector
{
    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        if ($this->providers === null) {
            throw new RuntimeException(self::class . ' is not configured.');
        }
        if ($this->providers->count() === 0) {
            return $nextProvider === null ? null : $nextProvider->read();
        }
        return $this->providers[0]->read($nextProvider);
    }
}
