<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider\Support;

use RuntimeException;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

/**
 * A class for working with a group of schema providers.
 * Parts of the schema are read from all providers and merged into one.
 */
final class MergeSchemaProvider extends BaseProviderCollector
{
    protected const IS_SEQUENCE_PIPELINE = false;

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        if ($this->providers === null) {
            throw new RuntimeException(self::class . ' is not configured.');
        }
        $parts = [];
        foreach ($this->providers as $provider) {
            $parts[] = $provider->read();
        }

        $schema = (new SchemaMerger())->merge(...$parts);

        if ($schema !== null || $nextProvider === null) {
            return $schema;
        }
        return  $nextProvider->read();
    }
}
