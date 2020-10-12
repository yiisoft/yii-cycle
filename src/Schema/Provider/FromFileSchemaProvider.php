<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

/**
 * Be careful, using this class may be insecure.
 */
final class FromFileSchemaProvider implements SchemaProviderInterface
{
    private string $file = '';
    private Aliases $aliases;

    public function __construct(Aliases $aliases)
    {
        $this->aliases = $aliases;
    }

    public function withConfig(array $config): self
    {
        $new = clone $this;
        // required option
        $new->file = $this->aliases->get($config['file']);
        return $new;
    }

    public function read(): ?array
    {
        if (!is_file($this->file)) {
            return null;
        }
        return include $this->file;
    }

    public function write(array $schema): bool
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
