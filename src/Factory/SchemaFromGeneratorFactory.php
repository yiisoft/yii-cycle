<?php

namespace Yiisoft\Yii\Cycle\Factory;

use Closure;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\Schema;
use Cycle\Schema\Compiler;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Database\DatabaseManager;
use Yiisoft\Yii\Cycle\SchemaConveyorInterface;

final class SchemaFromGeneratorFactory
{
    public string $cacheKey;
    public bool $cacheEnabled;
    /** @var GeneratorInterface[]|string[]|Closure[] */
    private array $generators;

    public function __construct(
        bool $cacheEnabled = true,
        string $cacheKey = 'Cycle-ORM-Schema',
        array $generators = []
    ) {
        $this->cacheEnabled = $cacheEnabled;
        $this->cacheKey = $cacheKey;
        $this->generators = $generators;
    }

    /**
     * @param ContainerInterface $container
     * @return Schema
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException
     */
    public function __invoke(ContainerInterface $container)
    {
        $schemaArray = null;
        if ($this->cacheEnabled) {
            $container->get(CacheInterface::class)->get($this->cacheKey);
        }
        if (!is_array($schemaArray)) {
            $schemaArray = $this->generateSchemaArray(
                $container->get(SchemaConveyorInterface::class),
                $container->get(DatabaseManager::class)
            );
        }
        if ($this->cacheEnabled) {
            $container->get(CacheInterface::class)->set($this->cacheKey, $schemaArray);
        }
        return new Schema($schemaArray);
    }

    /**
     * @param SchemaConveyorInterface $conveyor
     * @param DatabaseManager $dbal
     * @return array
     * @throws \Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException
     */
    private function generateSchemaArray(SchemaConveyorInterface $conveyor, DatabaseManager $dbal): array
    {
        // add generators to userland stage
        foreach ($this->generators as $generator) {
            $conveyor->addGenerator(SchemaConveyorInterface::STAGE_USERLAND, $generator);
        }
        // compile schema array
        $generators = $conveyor->getGenerators();
        return (new Compiler())->compile(new Registry($dbal), $generators);
    }
}
