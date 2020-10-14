<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Provider\PhpFileSchemaProvider;

use InvalidArgumentException;
use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Schema\Provider\PhpFileSchemaProvider;
use PHPUnit\Framework\TestCase;

class PhpFileSchemaProviderTest extends TestCase
{
    private const DEFAULT_CONFIG = ['file' => '@dir/simple_schema.php'];
    private const WRITE_CONFIG = ['file' => self::TMP_FILE];
    private const DEFAULT_SCHEMA = ['user' => []];
    private const TMP_FILE = __DIR__ . '/files/write.php';

    protected function setUp(): void
    {
        $this->removeTmpFile();
    }
    protected function tearDown(): void
    {
        $this->removeTmpFile();
    }

    public function testDefaultState(): void
    {
        $provider = $this->createSchemaProvider();

        $this->assertTrue($provider->isReadable());
        $this->assertTrue($provider->isWritable());
        $this->assertNull($provider->read());
    }
    public function testWithConfigImmutable(): void
    {
        $provider = $this->createSchemaProvider();
        $newProvider = $provider->withConfig(self::DEFAULT_CONFIG);

        $this->assertNotSame($provider, $newProvider);
        $this->assertNull($provider->read());
        $this->assertSame(self::DEFAULT_SCHEMA, $newProvider->read());
    }
    public function testWithConfigWithoutRequiredParams(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createSchemaProvider([]);
    }
    public function testModeWriteOnly(): void
    {
        $config = self::DEFAULT_CONFIG;
        $config['mode'] = PhpFileSchemaProvider::MODE_WRITE_ONLY;
        $provider = $this->createSchemaProvider($config);

        $this->assertFalse($provider->isReadable());
        $this->assertTrue($provider->isWritable());
    }
    public function testModeWriteOnlyExceptionOnRead(): void
    {
        $config = self::DEFAULT_CONFIG;
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
        $this->assertFalse($this->isTmpFileExists());
    }
    public function testClearNotExistingFile(): void
    {
        $provider = $this->createSchemaProvider(self::WRITE_CONFIG);

        $result = $provider->clear();

        $this->assertTrue($result);
    }
    public function testClearNotFile(): void
    {
        $provider = $this->createSchemaProvider(['file' => '@dir']);

        $result = $provider->clear();

        $this->assertFalse($result);
    }
    public function testWrite(): void
    {
        $provider = $this->createSchemaProvider(self::WRITE_CONFIG);

        $result = $provider->write([]);

        $this->assertTrue($result);
        $this->assertTrue($this->isTmpFileExists());
    }

    public function testPrepareTmpFile(): void
    {
        $this->prepareTmpFile();
        $this->assertTrue($this->isTmpFileExists());
    }
    public function testRemoveTmpFile(): void
    {
        $this->prepareTmpFile();
        $this->removeTmpFile();
        $this->assertFalse($this->isTmpFileExists());
    }
    private function isTmpFileExists(): bool
    {
        return is_file(self::TMP_FILE);
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
    private function createSchemaProvider(array $config = null): PhpFileSchemaProvider
    {
        $aliases = new Aliases(['@dir' => __DIR__ . '/files']);
        $provider = new PhpFileSchemaProvider($aliases);
        return $config === null ? $provider : $provider->withConfig($config);
    }
}
