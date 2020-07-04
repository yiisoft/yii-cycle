<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Closure;
use Cycle\Schema\Compiler;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class FromConveyorSchemaProvider implements SchemaProviderInterface
{
    private SchemaConveyorInterface $conveyor;
    private DatabaseManager $dbal;
    /**
     * Additional generators when reading Schema
     * @var string[]|GeneratorInterface[]|Closure[]
     */
    private array $generators = [];

    public function __construct(SchemaConveyorInterface $conveyor, DatabaseManager $dbal)
    {
        $this->conveyor = $conveyor;
        $this->dbal = $dbal;
    }

    public function withConfig(array $config): SchemaProviderInterface
    {
        $clone = clone $this;
        $clone->generators = $config['generators'] ?? [];
        return $clone;
    }

    public function read(): ?array
    {
        $generators = $this->getGenerators();
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

    private function getGenerators(): array
    {
        $conveyor = clone $this->conveyor;
        // add generators to userland stage
        foreach ($this->generators as $generator) {
            $conveyor->addGenerator(SchemaConveyorInterface::STAGE_USERLAND, $generator);
        }
        return $conveyor->getGenerators();
    }
}
