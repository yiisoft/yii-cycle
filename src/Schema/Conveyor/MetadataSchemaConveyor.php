<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Exception\EmptyEntityPathsException;

abstract class MetadataSchemaConveyor extends SchemaConveyor
{
    /** @var string[] */
    private array $entityPaths = [];

    private int $tableNaming = Entities::TABLE_NAMING_SINGULAR;

    private bool $isAddedMetadataGenerators = false;

    final public function setTableNaming(
        #[ExpectedValues(valuesFromClass: Entities::class)]
        int $type
    ): void {
        $this->tableNaming = $type;
    }

    final public function getTableNaming(): int
    {
        return $this->tableNaming;
    }

    /**
     * @param string[] $paths
     */
    final public function addEntityPaths(array $paths): void
    {
        $this->entityPaths = array_merge($this->entityPaths, $paths);
    }

    public function getGenerators(): array
    {
        $this->addMetadataGenerators();
        return parent::getGenerators();
    }

    abstract protected function getMetadataReader(): ?ReaderInterface;

    /**
     * Add some generators in this conveyor into the INDEX stage
     * Added generators will search for entity classes and read their annotations
     */
    private function addMetadataGenerators(): void
    {
        if ($this->isAddedMetadataGenerators) {
            return;
        }
        $classLocator = $this->getEntityClassLocator();

        $reader = $this->getMetadataReader();

        // register embeddable entities
        $this->conveyor[SchemaConveyor::STAGE_INDEX][] = new Embeddings($classLocator, $reader);
        // register annotated entities
        $this->conveyor[SchemaConveyor::STAGE_INDEX][] = new Entities($classLocator, $reader, $this->tableNaming);
        // add @Table(columns) declarations
        $this->conveyor[SchemaConveyor::STAGE_INDEX][] = new MergeColumns($reader);
        // add @Table(indexes) declarations
        $this->conveyor[SchemaConveyor::STAGE_RENDER][] = new MergeIndexes($reader);

        $this->isAddedMetadataGenerators = true;
    }

    private function getEntityClassLocator(): ClassLocator
    {
        $aliases = $this->container->get(Aliases::class);
        $list = [];
        foreach ($this->entityPaths as $path) {
            $list[] = $aliases->get($path);
        }

        if (!count($list)) {
            throw new EmptyEntityPathsException();
        }

        $finder = (new Finder())
            ->files()
            ->in($list);

        return new ClassLocator($finder);
    }
}
