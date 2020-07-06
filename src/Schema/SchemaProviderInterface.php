<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

interface SchemaProviderInterface
{
    public function withConfig(array $config): self;
    
    public function isWritable(): bool;
    
    public function isReadable(): bool;
    
    public function read(): ?array;
    
    public function write(array $schema): bool;
    
    public function clear(): bool;
}
