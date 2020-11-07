<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Converter;

use Cycle\ORM\Schema;
use Yiisoft\Yii\Cycle\Schema\Converter\SchemaToPHP;
use PHPUnit\Framework\TestCase;

class SchemaToPHPTest extends TestCase
{
    public function testSchemaComplex(): void
    {
        $file = __DIR__ . '/file/schema1.php';
        $schemaString = file_get_contents($file);
        $schema = include $file;
        $result = $this->createConverter($schema)->convert();

        $this->assertSame(strlen($schemaString), strlen($result), 'Length comparison');
        $this->assertSame($schemaString, $result);
    }

    private function createConverter(array $schema): SchemaToPHP
    {
        return new SchemaToPHP(new Schema($schema));
    }
}
