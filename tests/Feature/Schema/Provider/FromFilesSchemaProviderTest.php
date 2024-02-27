<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider;

use Cycle\ORM\SchemaInterface as Schema;
use Cycle\Schema\Provider\FromFilesSchemaProvider;

final class FromFilesSchemaProviderTest extends BaseTestCase
{
    public function testReadWithAlias(): void
    {
        $schemaProvider = $this->container->get(FromFilesSchemaProvider::class);

        $data = $schemaProvider
            ->withConfig(['files' => ['@test/schema1.php', '@test/schema2.php']])
            ->read();

        $this->assertSame([
            'user' => [],
            'post' => [Schema::DATABASE => 'postgres'],
            'comment' => [],
        ], $data);
    }
}
