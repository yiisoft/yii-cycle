<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Converter;

use Cycle\ORM\Relation;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Relation\RelationSchema;
use Yiisoft\Yii\Cycle\Schema\Converter\SchemaToPHP\SchemaRenderer;

final class SchemaToPHP
{
    private SchemaInterface $schema;
    private const USE_LIST = [
        Schema::class,
        Relation::class,
        RelationSchema::class,
    ];

    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    public function __toString(): string
    {
        return $this->convert();
    }

    public function convert(): string
    {
        $result = "<?php\n\ndeclare(strict_types=1);\n\n";
        // the use block
        foreach (self::USE_LIST as $use) {
            $result .= "use {$use};\n";
        }
        $renderedArray = (new SchemaRenderer($this->schema))->render();
        $result .= "\nreturn {$renderedArray};\n";
        return $result;
    }
}
