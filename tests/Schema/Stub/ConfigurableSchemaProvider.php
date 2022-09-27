<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Stub;

use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

class ConfigurableSchemaProvider implements SchemaProviderInterface
{
    public const OPTION_WRITABLE = 'writable';
    public const OPTION_CLEARABLE = 'clearable';
    public const EXCEPTION_ON_READ = 'exception_on_read';
    public const EXCEPTION_ON_WRITE = 'exception_on_write';
    public const EXCEPTION_ON_CLEAR = 'exception_on_clear';

    private bool $writable = true;
    private bool $clearable = true;
    private bool $exceptionOnRead = false;
    private bool $exceptionOnWrite = false;
    private bool $exceptionOnClear = false;

    public function __construct(protected ?array $schema)
    {
    }

    public function withConfig(array $config): self
    {
        $new = clone $this;
        if (array_key_exists(self::OPTION_WRITABLE, $config)) {
            $new->writable = $config[self::OPTION_WRITABLE];
        }
        if (array_key_exists(self::OPTION_CLEARABLE, $config)) {
            $new->clearable = $config[self::OPTION_CLEARABLE];
        }
        if (array_key_exists(self::EXCEPTION_ON_READ, $config)) {
            $new->exceptionOnRead = $config[self::EXCEPTION_ON_READ];
        }
        if (array_key_exists(self::EXCEPTION_ON_WRITE, $config)) {
            $new->exceptionOnWrite = $config[self::EXCEPTION_ON_WRITE];
        }
        if (array_key_exists(self::EXCEPTION_ON_CLEAR, $config)) {
            $new->exceptionOnClear = $config[self::EXCEPTION_ON_CLEAR];
        }
        return $new;
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function isExceptionOnRead(): bool
    {
        return $this->exceptionOnRead;
    }

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        if ($this->exceptionOnRead) {
            throw new \RuntimeException('Schema cannot be read.');
        }
        if ($this->schema !== null) {
            return $this->schema;
        }
        $schema = $nextProvider === null ? null : $nextProvider->read();
        if ($schema !== null) {
            $this->write($schema);
        }
        return $schema;
    }

    public function write(array $schema): bool
    {
        if ($this->exceptionOnWrite) {
            throw new \RuntimeException('Schema cannot be written.');
        }
        if (!$this->writable) {
            return false;
        }
        $this->schema = $schema;
        return true;
    }

    public function clear(): bool
    {
        if ($this->exceptionOnClear) {
            throw new \RuntimeException('Schema cannot be cleared.');
        }
        if (!$this->clearable) {
            return false;
        }
        $this->schema = null;
        return true;
    }
}
