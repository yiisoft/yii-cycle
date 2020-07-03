<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

/**
 * Be careful, using this class may be insecure.
 */
final class FromFileSchemaProvider implements SchemaProviderInterface
{
    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function read(): ?array
    {
        if (!is_file($this->file)) {
            throw new \RuntimeException('Schema file not found.');
        }
        return include $this->file;
    }

    public function write($schema): bool
    {
        return false;
    }

    public function clear(): bool
    {
        return false;
    }
    public function isWritable(): bool
    {
        return false;
    }
    public function isReadable(): bool
    {
        return true;
    }
}
