<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use InvalidArgumentException;
use LogicException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

/**
 * Be careful, using this class may be insecure.
 */
final class FromFileSchemaProvider implements SchemaProviderInterface
{
    private array $files = [];
    private Aliases $aliases;

    public function __construct(Aliases $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * @param array $config
     * @return self
     * @throws InvalidArgumentException
     */
    public function withConfig(array $config): self
    {
        if (empty($config['file'])) {
            throw new InvalidArgumentException('File(s) not set.');
        }

        if (is_string($config['file'])) {
            $files = [$config['file']];
        } elseif (is_array($config['file'])) {
            $files = $config['file'];
        } else {
            throw new InvalidArgumentException('The "file" parameter must be array or string.');
        }

        $files = array_map(
            fn ($file) => $this->aliases->get($file),
            $files
        );

        $new = clone $this;
        $new->files = $files;
        return $new;
    }

    /**
     * @return array|null
     * @throws LogicException
     */
    public function read(): ?array
    {
        $schema = [];
        foreach ($this->files as $file) {
            if (is_file($file)) {
                foreach (require $file as $role => $definition) {
                    if (array_key_exists($role, $schema)) {
                        throw new LogicException('Role "' . $role . '" already has in schema.');
                    }
                    $schema[$role] = $definition;
                }
            }
        }
        return empty($schema) ? null : $schema;
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
