<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Stub;

use Cycle\Schema\Provider\SchemaProviderInterface;

final class ArraySchemaProvider implements SchemaProviderInterface
{
    protected ?array $schema;

    public function __construct(array $schema = null)
    {
        $this->schema = $schema;
    }

    /**
     * @param array $config will replace the schema
     */
    public function withConfig(array $config): self
    {
        $new = clone $this;
        $new->schema = $config;
        return $new;
    }

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        if ($this->schema !== null) {
            return $this->schema;
        }
        $this->schema = $nextProvider?->read();
        return $this->schema;
    }

    public function clear(): bool
    {
        $this->schema = null;
        return true;
    }
}
