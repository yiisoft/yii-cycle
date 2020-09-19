<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider\FromFilesSchemaProvider;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Exception\DuplicateRoleException;
use Yiisoft\Yii\Cycle\Exception\SchemaFileNotFoundException;
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
        $this->expectExceptionMessage('Schema file list is not set.');
        $schemaProvider->withConfig($config);
    }

    public function testWithConfigInvalidFiles(): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "files" parameter must be an array.');
        $schemaProvider->withConfig(['files' => '@dir/schema1.php']);
    }

    public function testWithConfigInvalidStrict(): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "strict" parameter must be a boolean.');
        $schemaProvider->withConfig([
            'files' => ['@dir/schema1.php'],
            'strict' => 1,
        ]);
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

    public function testWithConfigFilesNotExists(): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $data = $schemaProvider
            ->withConfig(['files' => ['@dir/schema-not-exists.php']])
            ->read();

        $this->assertNull($data);
    }

    public function testWithConfigFilesEmpty(): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $data = $schemaProvider
            ->withConfig(['files' => ['@dir/schema-empty.php']])
            ->read();

        $this->assertSame([], $data);
    }

    public function testWithConfigStrictFilesNotExists(): void
    {
        $schemaProvider = $this
            ->createSchemaProvider()
            ->withConfig([
                'files' => [
                    '@dir/schema1.php',
                    '@dir/schema-not-exists.php',
                ],
                'strict' => true,
            ]);

        $this->expectException(SchemaFileNotFoundException::class);
        $schemaProvider->read();
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
                    '@dir/schema-not-exists.php', // not exists files should be silent in non strict mode
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

        $this->expectException(DuplicateRoleException::class);
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
