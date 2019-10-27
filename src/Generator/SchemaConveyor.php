<?php

namespace Yiisoft\Yii\Cycle\Generator;

use Cycle\Schema\Generator;
use Cycle\Schema\GeneratorInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;
use Yiisoft\Yii\Cycle\SchemaConveyorInterface;

class SchemaConveyor implements SchemaConveyorInterface
{
    /** @var array */
    protected $conveyor = [];
    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->conveyor = [
            self::STAGE_INDEX => [],
            self::STAGE_RENDER => [
                // render tables and relations
                Generator\ResetTables::class,       // re-declared table schemas (remove columns)
                Generator\GenerateRelations::class, // generate entity relations
                Generator\ValidateEntities::class,  // make sure all entity schemas are correct
                Generator\RenderTables::class,      // declare table schemas
                Generator\RenderRelations::class,   // declare relation keys and indexes
            ],
            self::STAGE_USERLAND => [],
            self::STAGE_POSTPROCESS => [
                // post processing
                Generator\GenerateTypecast::class   // typecast non string columns
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function addGenerator(string $stage, $generator): void
    {
        $this->conveyor[$stage][] = $generator;
    }

    /**
     * @inheritDoc
     */
    public function getGenerators(): array
    {
        $result = [];
        foreach ($this->conveyor as $group) {
            foreach ($group as $generator) {
                $g = null;
                if (is_string($generator)) {
                    $g = $this->container->get($generator);
                } elseif (is_object($generator) && $generator instanceof GeneratorInterface) {
                    $result[] = $generator;
                    continue;
                } elseif (is_object($generator) && method_exists($generator, '__invoke')) {
                    $g = $generator($this->container);
                }
                if ($g instanceof GeneratorInterface) {
                    $result[] = $g;
                    continue;
                }
                throw new BadDeclarationException();
            }
        }
        return $result;
    }
}
