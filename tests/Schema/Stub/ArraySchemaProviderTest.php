<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Stub;

use Yiisoft\Yii\Cycle\Tests\Schema\Provider\BaseSchemaProviderTest;

final class ArraySchemaProviderTest extends BaseSchemaProviderTest
{
    protected const READ_CONFIG = self::DEFAULT_CONFIG_SCHEMA;

    protected function createSchemaProvider(?array $config = null): ArraySchemaProvider
    {
        return new ArraySchemaProvider($config);
    }
}
