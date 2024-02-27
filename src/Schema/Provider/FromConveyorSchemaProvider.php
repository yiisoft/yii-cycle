<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Closure;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Schema\Compiler;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Provider\SchemaProviderInterface;
use Cycle\Schema\Registry;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;

final class FromConveyorSchemaProvider implements SchemaProviderInterface
{
    /**
     * Additional generators when reading Schema
     *
     * @var Closure[]|GeneratorInterface[]|string[]
     */
    private array $generators = [];

    public function __construct(
        private SchemaConveyorInterface $conveyor,
        private DatabaseProviderInterface $dbal,
    ) {
    }

    /**
     * @param list<Closure|GeneratorInterface|string> $generators
     *        Additional {@see SchemaConveyorInterface::STAGE_USERLAND} generators
     */
    public static function config(array $generators): array
    {
        return [
            'generators' => $generators,
        ];
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

        return count($schema) !== 0 || $nextProvider === null ? $schema : $nextProvider->read();
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
