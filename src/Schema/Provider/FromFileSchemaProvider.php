<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

/**
 * Be careful, using this class may be insecure.
 *
 * @deprecated use {@see FromFilesSchemaProvider} instead
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

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        if (!is_file($this->file)) {
            return null;
        }
        $schema = include $this->file;
        return $schema !== null && $nextProvider === null ? $schema : $nextProvider->read();
    }

    public function clear(): bool
    {
        return false;
    }
}
