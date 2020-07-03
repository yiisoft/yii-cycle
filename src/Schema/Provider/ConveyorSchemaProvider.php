<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class ConveyorSchemaProvider implements SchemaProviderInterface
{
    private SchemaConveyorInterface $conveyor;
    private DatabaseManager $dbal;

    public function __construct(SchemaConveyorInterface $conveyor, DatabaseManager $dbal)
    {
        $this->conveyor = $conveyor;
        $this->dbal = $dbal;
    }

    public function read(): ?array
    {
        $generators = $this->conveyor->getGenerators();
        return (new Compiler())->compile(new Registry($this->dbal), $generators);
    }

    public function write($schema): bool
    {
        return false;
    }

    public function clear(): bool
    {
        return false;
    }
    public function isWritable(): bool
    {
        return false;
    }
    public function isReadable(): bool
    {
        return true;
    }
}
