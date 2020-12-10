<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Stub;

final class SameOriginProvider extends ConfigurableSchemaProvider
{
    public function withConfig(array $config): self
    {
        $new = parent::withConfig($config);
        $new->schema = &$this->schema;
        return $new;
    }
}
