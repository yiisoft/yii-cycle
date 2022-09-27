<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Listener\Stub;

use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

class CallingSpyProvider implements SchemaProviderInterface
{
    private int $read = 0;
    private int $write = 0;
    private int $clear = 0;

    public function __construct(private array $schema = null)
    {
    }

    public function withConfig(array $config): self
    {
        return $this;
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        ++$this->read;
        return $this->schema;
    }

    public function write(array $schema): bool
    {
        ++$this->write;
        $this->schema = $schema;
        return true;
    }

    public function clear(): bool
    {
        ++$this->clear;
        $this->schema = null;
        return true;
    }

    public function getReadCount(): int
    {
        return $this->read;
    }

    public function getWriteCount(): int
    {
        return $this->write;
    }

    public function getClearCount(): int
    {
        return $this->clear;
    }
}
