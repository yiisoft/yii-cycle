<?php

namespace Yiisoft\Yii\Cycle\Model;

use Cycle\Annotated;
use Psr\Container\ContainerInterface;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;
use Yiisoft\Aliases\Aliases;

class AnnotatedSchemaConveyor extends SchemaConveyor
{
    /** @var string[] */
    protected $entityPaths = [];

    /** @var int */
    protected $tableNaming = Annotated\Entities::TABLE_NAMING_SINGULAR;

    protected $isAnnotated = false;

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
    }

    public function setTableNaming(int $type): void
    {
        $this->tableNaming = $type;
    }

    public function getTableNaming(): int
    {
        return $this->tableNaming;
    }

    /**
     * @param string|string[] $paths
     */
    public function addEntityPaths($paths): void
    {
        $paths = (array)$paths;
        $this->entityPaths = array_merge($this->entityPaths, $paths);
    }

    public function getGenerators(): array
    {
        $this->annotateConveyor();
        return parent::getGenerators();
    }

    protected function annotateConveyor()
    {
        if ($this->isAnnotated) {
            return;
        }
        $this->isAnnotated = true;
        $classLocator = $this->getEntityClassLocator();

        // register embeddable entities
        $this->conveyor[SchemaConveyor::STAGE_INDEX][] = new Annotated\Embeddings($classLocator);
        // register annotated entities
        $this->conveyor[SchemaConveyor::STAGE_INDEX][] = new Annotated\Entities($classLocator, null, $this->tableNaming);
        // add @Table(columns) declarations
        $this->conveyor[SchemaConveyor::STAGE_INDEX][] = Annotated\MergeColumns::class;
        // add @Table(indexes) declarations
        $this->conveyor[SchemaConveyor::STAGE_RENDER][] = Annotated\MergeIndexes::class;
    }

    protected function getEntityClassLocator(): ClassLocator
    {
        $aliases = $this->container->get(Aliases::class);
        $list = [];
        foreach ($this->entityPaths as $path) {
            $list[] = $aliases->get($path);
        }
        $finder = (new Finder())
            ->files()
            ->in($list);

        return new ClassLocator($finder);
    }
}
