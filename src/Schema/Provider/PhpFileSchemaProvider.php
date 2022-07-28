<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Cycle\Schema\Renderer\PhpSchemaRenderer;
use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class PhpFileSchemaProvider implements SchemaProviderInterface
{
    public const MODE_READ_AND_WRITE = 0;
    public const MODE_WRITE_ONLY = 1;

    private string $file = '';
    private int $mode = self::MODE_READ_AND_WRITE;

    private Aliases $aliases;

    public function __construct(Aliases $aliases)
    {
        $this->aliases = $aliases;
    }

    public function withConfig(array $config): self
    {
        $new = clone $this;

        // required option
        if ($this->file === '' && !array_key_exists('file', $config)) {
            throw new \InvalidArgumentException('The "file" parameter is required.');
        }
        $new->file = $this->aliases->get($config['file']);

        $new->mode = $config['mode'] ?? $this->mode;

        return $new;
    }

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        if (!$this->isReadable()) {
            if ($nextProvider === null) {
                throw new RuntimeException(__CLASS__ . ' can not read schema.');
            }
            $schema = null;
        } else {
            $schema = !is_file($this->file) ? null : (include $this->file);
        }

        if ($schema !== null || $nextProvider === null) {
            return $schema;
        }

        $schema = $nextProvider->read();
        if ($schema !== null) {
            $this->write($schema);
        }
        return $schema;
    }

    private function write(array $schema): bool
    {
        if (basename($this->file) === '') {
            throw new RuntimeException('The "file" parameter must not be empty.');
        }
        $dirname = dirname($this->file);
        if ($dirname !== '' && !is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        $content = (new PhpSchemaRenderer())->render($schema);
        file_put_contents($this->file, $content, LOCK_EX);
        return true;
    }

    private function removeFile(): void
    {
        if (!file_exists($this->file)) {
            return;
        }
        if (!is_file($this->file)) {
            throw new RuntimeException("`$this->file` is not a file.");
        }
        if (!is_writable($this->file)) {
            throw new RuntimeException("File `$this->file` is not writeable.");
        }
        unlink($this->file);
    }

    public function clear(): bool
    {
        try {
            $this->removeFile();
        } catch (\Throwable $e) {
            return false;
        }
        return true;
    }

    private function isReadable(): bool
    {
        return $this->mode !== self::MODE_WRITE_ONLY;
    }
}
