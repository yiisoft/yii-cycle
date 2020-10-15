<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Closure;
use Cycle\Schema\Compiler;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Spiral\Database\DatabaseProviderInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class FromConveyorSchemaProvider implements SchemaProviderInterface
{
    private SchemaConveyorInterface $conveyor;
    private DatabaseProviderInterface $dbal;
    /**
     * Additional generators when reading Schema
     * @var string[]|GeneratorInterface[]|Closure[]
     */
    private array $generators = [];

    public function __construct(SchemaConveyorInterface $conveyor, DatabaseProviderInterface $dbal)
    {
        $this->conveyor = $conveyor;
        $this->dbal = $dbal;
    }

    public function withConfig(array $config): self
    {
        $new = clone $this;
        $new->generators = $config['generators'] ?? [];
        return $new;
    }

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        $generators = $this->getGenerators();
        $schema = (new Compiler())->compile(new Registry($this->dbal), $generators);

        return $schema !== null || $nextProvider === null ? $schema : $nextProvider->read();
    }

    public function clear(): bool
    {
        return false;
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
