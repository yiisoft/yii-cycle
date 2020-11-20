<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

use Throwable;

interface SchemaProviderInterface
{
    public function withConfig(array $config): self;

    public function read(?SchemaProviderInterface $nextProvider = null): ?array;

    /**
     * @return bool TRUE if the provider is writeable and the schema has been cleared; FALSE if the provider should not
     * clean up the schema.
     *
     * @throws Throwable Any error occurred while trying to clear the schema.
     */
    public function clear(): bool;
}
