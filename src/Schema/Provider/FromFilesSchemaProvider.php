<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Generator;
use InvalidArgumentException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Exception\SchemaFileNotFoundException;
use Yiisoft\Yii\Cycle\Schema\Provider\Support\SchemaMerger;
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

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        $schema = (new SchemaMerger())->merge(...$this->readFiles());

        return $schema !== null || $nextProvider === null ? $schema : $nextProvider->read();
    }

    public function clear(): bool
    {
        return false;
    }

    /**
     * Read schema from each file
     * @return Generator<int, array|null>
     */
    private function readFiles(): Generator
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                yield require $file;
            } elseif ($this->strict) {
                throw new SchemaFileNotFoundException($file);
            }
        }
    }
}
