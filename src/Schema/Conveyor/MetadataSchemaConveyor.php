<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Conveyor;

use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\Locator\TokenizerEmbeddingLocator;
use Cycle\Annotated\Locator\TokenizerEntityLocator;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Annotated\TableInheritance;
use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Exception\EmptyEntityPathsException;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface as Conveyor;

final class MetadataSchemaConveyor extends SchemaConveyor
{
    /** @var string[] */
    private array $entityPaths = [];

    private int $tableNaming = Entities::TABLE_NAMING_SINGULAR;

    private bool $isAddedMetadataGenerators = false;

    public function getTableNaming(): int
    {
        return $this->tableNaming;
    }

    public function setTableNaming(
        #[ExpectedValues(valuesFromClass: Entities::class)]
        int $type
    ): void {
        $this->tableNaming = $type;
    }

    /**
     * @param string[] $paths
     */
    public function addEntityPaths(array $paths): void
    {
        $this->entityPaths = array_merge($this->entityPaths, $paths);
    }

    #[\Override]
    public function getGenerators(): array
    {
        $this->addMetadataGenerators();
        return parent::getGenerators();
    }

    protected function getMetadataReader(): ?ReaderInterface
    {
        return new AttributeReader();
    }

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
        $tokenizerEmbeddingLocator = new TokenizerEmbeddingLocator($classLocator, $reader);
        $tokenizerEntityLocator = new TokenizerEntityLocator($classLocator, $reader);

        // register embeddable entities
        $this->conveyor[Conveyor::STAGE_INDEX][] = new Embeddings($tokenizerEmbeddingLocator, $reader);
        // register annotated entities
        $this->conveyor[Conveyor::STAGE_INDEX][] = new Entities($tokenizerEntityLocator, $reader, $this->tableNaming);
        // register STI/JTI
        $this->conveyor[Conveyor::STAGE_INDEX][] = new TableInheritance($reader);
        // add @Table(columns) declarations
        $this->conveyor[Conveyor::STAGE_INDEX][] = new MergeColumns($reader);
        // add @Table(indexes) declarations
        $this->conveyor[Conveyor::STAGE_RENDER][] = new MergeIndexes($reader);

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
