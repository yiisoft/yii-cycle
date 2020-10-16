<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Cycle\ORM\Schema;
use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Schema\Converter\SchemaToPHP;
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

    public function read(): ?array
    {
        if ($this->mode === self::MODE_WRITE_ONLY) {
            throw new RuntimeException(__CLASS__ . ' can not read schema.');
        }
        if (!is_file($this->file)) {
            return null;
        }
        return include $this->file;
    }

    public function write(array $schema): bool
    {
        if (basename($this->file) === '') {
            throw new RuntimeException('The "file" parameter must not be empty.');
        }
        $dirname = dirname($this->file);
        if ($dirname !== '' && !is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        $content = (new SchemaToPHP(new Schema($schema)))->convert();
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

    public function isWritable(): bool
    {
        return true;
    }

    public function isReadable(): bool
    {
        return $this->mode !== self::MODE_WRITE_ONLY;
    }
}
