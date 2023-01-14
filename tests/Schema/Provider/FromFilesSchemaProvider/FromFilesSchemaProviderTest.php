<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider\FromFilesSchemaProvider;

use Cycle\ORM\SchemaInterface as Schema;
use InvalidArgumentException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Exception\DuplicateRoleException;
use Yiisoft\Yii\Cycle\Exception\SchemaFileNotFoundException;
use Yiisoft\Yii\Cycle\Schema\Provider\FromFilesSchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Schema\Provider\BaseSchemaProviderTest;

class FromFilesSchemaProviderTest extends BaseSchemaProviderTest
{
    protected const READ_CONFIG = ['files' => ['@dir/schema1.php']];
    protected const READ_CONFIG_SCHEMA = ['user' => []];

    public function EmptyConfigProvider(): array
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
     * @dataProvider EmptyConfigProvider
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

    public function FileListBadValuesProvider(): array
    {
        return [
            [null],
            [42],
            [STDIN],
            [[]],
            [new \SplFileInfo(__FILE__)],
        ];
    }

    /**
     * @dataProvider FileListBadValuesProvider
     */
    public function testWithConfigInvalidValueInFileList($value): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "files" parameter must contain string values.');
        $schemaProvider->withConfig(['files' => [$value]]);
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
            'post' => [Schema::DATABASE => 'postgres'],
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

    public function testReadWildcard(): void
    {
        $schemaProvider = $this->createSchemaProvider();

        $data = $schemaProvider
            ->withConfig([
                'strict' => true,
                'files' => [
                    '@dir/schema[12].php',
                    '@dir/*/*.php', // no files found
                    '@dir/**/level3*.php',
                ],
            ])
            ->read();

        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('post', $data);
        $this->assertArrayHasKey('level3-schema', $data);
        $this->assertArrayHasKey('level3-1-schema', $data);
        $this->assertArrayHasKey('level3-2-schema', $data);
        $this->assertArrayNotHasKey('level2-schema', $data);
    }

    public function testClear(): void
    {
        $schemaProvider = $this->createSchemaProvider();
        $this->assertFalse($schemaProvider->clear());
    }

    protected function createSchemaProvider(array $config = null): FromFilesSchemaProvider
    {
        $aliases = new Aliases(['@dir' => __DIR__ . '/files']);
        $provider = new FromFilesSchemaProvider($aliases);
        return $config === null ? $provider : $provider->withConfig($config);
    }
}
