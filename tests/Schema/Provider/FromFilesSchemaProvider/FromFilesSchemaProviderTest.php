<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider\FromFilesSchemaProvider;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Schema\Provider\FromFilesSchemaProvider;

class FromFilesSchemaProviderTest extends TestCase
{
    public function getWithConfigEmptyData(): array
    {
        return [
            [
                [],
            ],
            [
                ['files' => []],
            ],
        ];
    }

    /**
     * @dataProvider getWithConfigEmptyData
     *
     * @param array $config
     */
    public function testWithConfigEmpty(array $config): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Files not set.');
        $schemaProvider->withConfig($config);
    }

    public function testWithConfigInvalidData(): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "files" parameter must be an array.');
        $schemaProvider->withConfig(['files' => '@dir/schema1.php']);
    }

    public function testWithConfig(): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $data = $schemaProvider
            ->withConfig(['files' => ['@dir/schema1.php']])
            ->read();

        $this->assertSame([
            'user' => [],
        ], $data);
    }

    public function testWithConfigImmutable(): void
    {
        $schemaProvider1 = $this->createSchemaProvider();
        $schemaProvider2 = $schemaProvider1->withConfig([
            'files' => ['@dir/schema1.php'],
        ]);
        $this->assertNull($schemaProvider1->read());
        $this->assertSame([
            'user' => [],
        ], $schemaProvider2->read());
    }

    public function testRead(): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $data = $schemaProvider
            ->withConfig([
                'files' => [
                    '@dir/schema1.php',
                    '@dir/schema-not-exists.php', // not exists files should be silent
                    '@dir/schema2.php',
                ],
            ])
            ->read();

        $this->assertSame([
            'user' => [],
            'post' => [],
            'comment' => [],
        ], $data);
    }

    public function testReadEmpty(): void
    {
        $schemaProvider = $this->createSchemaProvider();
        $this->assertNull($schemaProvider->read());
    }

    public function testReadDuplicateRoles(): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The "post" role already exists in the DB schema.');
        $schemaProvider
            ->withConfig([
                'files' => [
                    '@dir/schema2.php',
                    '@dir/schema2-duplicate.php',
                ],
            ])
            ->read();
    }

    public function testWrite(): void
    {
        $schemaProvider = $this->createSchemaProvider();
        $this->assertFalse($schemaProvider->write([]));
    }

    public function testClear(): void
    {
        $schemaProvider = $this->createSchemaProvider();
        $this->assertFalse($schemaProvider->clear());
    }

    public function testIsWritable(): void
    {
        $schemaProvider = $this->createSchemaProvider();
        $this->assertFalse($schemaProvider->isWritable());
    }

    public function testIsReadable(): void
    {
        $schemaProvider = $this->createSchemaProvider();
        $this->assertTrue($schemaProvider->isReadable());
    }

    private function createSchemaProvider(): FromFilesSchemaProvider
    {
        $aliases = new Aliases(['@dir' => __DIR__ . '/files']);
        return new FromFilesSchemaProvider($aliases);
    }
}
