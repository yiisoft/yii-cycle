<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

interface SchemaProviderInterface
{
    /**
     * @param array $config
     * @return $this
     */
    public function withConfig(array $config): self;
    public function isWritable(): bool;
    public function isReadable(): bool;
    /**
     * Read schema array
     */
    public function read(): ?array;
    /**
     * Write schema array
     */
    public function write(array $schema): bool;
    /**
     * Clear stored schema
     */
    public function clear(): bool;
}
