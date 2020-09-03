<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\SchemaManager\Stub;

use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class ArraySchemaProvider implements SchemaProviderInterface
{
    protected ?array $schema;
    public function __construct(array $schema = null)
    {
        $this->schema = $schema;
    }
    /**
     * @param array $config will replace the schema
     * @return $this
     */
    public function withConfig(array $config): self
    {
        $new = clone $this;
        $new->schema = $config;
        return $new;
    }
    public function isWritable(): bool
    {
        return true;
    }
    public function isReadable(): bool
    {
        return true;
    }
    public function read(): ?array
    {
        return $this->schema;
    }
    public function write(array $schema): bool
    {
        $this->schema = $schema;
        return true;
    }
    public function clear(): bool
    {
        $this->schema = null;
        return true;
    }
}
