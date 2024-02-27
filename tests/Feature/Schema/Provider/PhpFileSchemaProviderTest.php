<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider;

use Cycle\ORM\SchemaInterface as Schema;
use Cycle\Schema\Provider\PhpFileSchemaProvider;

final class PhpFileSchemaProviderTest extends BaseTestCase
{
    public function testReadWithAlias(): void
    {
        $schemaProvider = $this->container->get(PhpFileSchemaProvider::class);

        $data = $schemaProvider
            ->withConfig(['file' => '@test/schema2.php'])
            ->read();

        $this->assertSame([
            'post' => [Schema::DATABASE => 'postgres'],
            'comment' => [],
        ], $data);
    }
}
