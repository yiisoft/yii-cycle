<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use InvalidArgumentException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Exception\DuplicateRoleException;
use Yiisoft\Yii\Cycle\Exception\SchemaFileNotFoundException;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

/**
 * Be careful, using this class may be insecure.
 */
final class FromFilesSchemaProvider implements SchemaProviderInterface
{
    /** @var array Schema files */
    private array $files = [];

    /** @var bool Throw exception if file not found */
    private bool $strict = false;

    private Aliases $aliases;

    public function __construct(Aliases $aliases)
    {
        $this->aliases = $aliases;
    }

    public function withConfig(array $config): self
    {
        $files = $config['files'] ?? [];
        if (!is_array($files)) {
            throw new InvalidArgumentException('The "files" parameter must be an array.');
        }
        if (count($files) === 0) {
            throw new InvalidArgumentException('Schema file list is not set.');
        }

        $strict = $config['strict'] ?? $this->strict;
        if (!is_bool($strict)) {
            throw new InvalidArgumentException('The "strict" parameter must be a boolean.');
        }

        $files = array_map(
            function ($file) {
                if (!is_string($file)) {
                    throw new InvalidArgumentException('The "files" parameter must contain string values.');
                }
                return $this->aliases->get($file);
            },
            $files
        );

        $new = clone $this;
        $new->files = $files;
        $new->strict = $strict;
        return $new;
    }

    public function read(): ?array
    {
        $schema = null;

        foreach ($this->files as $file) {
            if (is_file($file)) {
                $schema = $schema ?? [];
                foreach (require $file as $role => $definition) {
                    if (array_key_exists($role, $schema)) {
                        throw new DuplicateRoleException($role);
                    }
                    $schema[$role] = $definition;
                }
            } elseif ($this->strict) {
                throw new SchemaFileNotFoundException($file);
            }
        }

        return $schema;
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
