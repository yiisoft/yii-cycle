<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider\Path;

use Cycle\Schema\Provider\Path\ResolverInterface;
use Yiisoft\Aliases\Aliases;

final class AliasesResolver implements ResolverInterface
{
    public function __construct(
        private Aliases $aliases,
    ) {
    }

    public function resolve(string $path): string
    {
        return $this->aliases->get($path);
    }
}
