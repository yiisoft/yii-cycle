<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider\PhpFileSchemaProvider;

use InvalidArgumentException;
use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Schema\Provider\PhpFileSchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider\BaseSchemaProvider;
use Yiisoft\Yii\Cycle\Tests\Feature\Schema\Stub\ArraySchemaProvider;

final class PhpFileSchemaProviderTest extends BaseSchemaProvider
{
    protected const READ_CONFIG = ['file' => '@dir/simple_schema.php'];
    protected const READ_CONFIG_SCHEMA = ['user' => []];
    private const WRITE_CONFIG = ['file' => self::TMP_FILE];
    private const WRITE_ONLY_CONFIG = ['file' => self::TMP_FILE, 'mode' => PhpFileSchemaProvider::MODE_WRITE_ONLY];
    private const TMP_FILE = __DIR__ . '/files/write.php';

    protected function setUp(): void
    {
        $this->removeTmpFile();
    }

    protected function tearDown(): void
    {
        $this->removeTmpFile();
    }

    public function testReadFromNextProvider(): void
    {
        $provider1 = $this->createSchemaProvider(self::WRITE_CONFIG);
        $provider2 = new ArraySchemaProvider(self::READ_CONFIG_SCHEMA);

        $result = $provider1->read($provider2);

        $this->assertSame(self::READ_CONFIG_SCHEMA, $result);
    }

    public function testDefaultState(): void
    {
        $provider = $this->createSchemaProvider();

        $this->assertNull($provider->read());
    }

    public function testWithConfigWithoutRequiredParams(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createSchemaProvider([]);
    }

    public function testWithConfigWithBadParams(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/parameter must not be empty/');

        $nextProvider = new ArraySchemaProvider(self::READ_CONFIG_SCHEMA);

        $this->createSchemaProvider(null)->read($nextProvider);
    }

    public function testModeWriteOnlyWithoutSchemaFromNextProvider(): void
    {
        $provider = $this->createSchemaProvider(self::WRITE_ONLY_CONFIG);
        $nextProvider = new ArraySchemaProvider(null);

        $this->assertNull($provider->read($nextProvider));
        $this->assertFileDoesNotExist(self::TMP_FILE, 'Empty schema file is created.');
    }

    public function testModeWriteOnlyWithSchemaFromNextProvider(): void
    {
        $provider = $this->createSchemaProvider(self::WRITE_ONLY_CONFIG);
        $nextProvider = new ArraySchemaProvider(self::READ_CONFIG_SCHEMA);
        $this->assertSame(self::READ_CONFIG_SCHEMA, $provider->read($nextProvider));
        $this->assertFileExists(self::TMP_FILE, 'Schema file is not created.');
    }

    public function testModeWriteOnlyWithoutNextProviderException(): void
    {
        $config = self::READ_CONFIG;
        $config['mode'] = PhpFileSchemaProvider::MODE_WRITE_ONLY;
        $provider = $this->createSchemaProvider($config);

        $this->expectException(RuntimeException::class);

        $provider->read();
    }

    public function testModeWriteOnlyExceptionOnRead(): void
    {
        $config = self::READ_CONFIG;
        $config['mode'] = PhpFileSchemaProvider::MODE_WRITE_ONLY;
        $provider = $this->createSchemaProvider($config);

        $this->expectException(RuntimeException::class);

        $provider->read();
    }

    public function testClear(): void
    {
        $this->prepareTmpFile();
        $provider = $this->createSchemaProvider(self::WRITE_CONFIG);

        $result = $provider->clear();

        $this->assertTrue($result);
        $this->assertFileDoesNotExist(self::TMP_FILE);
    }

    public function testClearNotExistingFile(): void
    {
        $provider = $this->createSchemaProvider(self::WRITE_CONFIG);

        $result = $provider->clear();

        $this->assertTrue($result);
    }

    public function testClearNotAFile(): void
    {
        $provider = $this->createSchemaProvider(['file' => '@dir']);

        $result = $provider->clear();

        $this->assertFalse($result);
    }

    public function testPrepareTmpFile(): void
    {
        $this->prepareTmpFile();
        $this->assertFileExists(self::TMP_FILE);
    }

    public function testRemoveTmpFile(): void
    {
        $this->prepareTmpFile();
        $this->removeTmpFile();
        $this->assertFileDoesNotExist(self::TMP_FILE);
    }

    private function prepareTmpFile(): void
    {
        file_put_contents(self::TMP_FILE, '<?php return null;');
    }

    private function removeTmpFile(): void
    {
        if (is_file(self::TMP_FILE)) {
            unlink(self::TMP_FILE);
        }
    }

    protected function createSchemaProvider(array $config = null): PhpFileSchemaProvider
    {
        $aliases = new Aliases(['@dir' => __DIR__ . '/files']);
        $provider = new PhpFileSchemaProvider($aliases);
        return $config === null ? $provider : $provider->withConfig($config);
    }
}
