<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Stub;

use Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider\BaseSchemaProvider;

final class ArraySchemaProviderTest extends BaseSchemaProvider
{
    protected const READ_CONFIG = self::READ_CONFIG_SCHEMA;

    protected function createSchemaProvider(?array $config = null): ArraySchemaProvider
    {
        return new ArraySchemaProvider($config);
    }
}
