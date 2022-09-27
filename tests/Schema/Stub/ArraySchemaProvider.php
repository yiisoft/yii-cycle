<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Stub;

use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class ArraySchemaProvider implements SchemaProviderInterface
{
    public function __construct(protected array $schema = null)
    {
    }

    /**
     * @param array $config will replace the schema
     *
     * @return $this
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
        $this->schema = $nextProvider === null ? null : $nextProvider->read();
        return $this->schema;
    }

    public function clear(): bool
    {
        $this->schema = null;
        return true;
    }
}
