<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

use Closure;
use Cycle\Schema\GeneratorInterface;
use Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException;

interface SchemaConveyorInterface
{
    // declare entities and their fields
    public const STAGE_INDEX = 'index';
    // render tables and relations
    public const STAGE_RENDER = 'render';
    // userland scripts
    public const STAGE_USERLAND = 'userland';
    // post processing
    public const STAGE_POSTPROCESS = 'postprocess';

    /**
     * @param string $stage
     * @param GeneratorInterface|string|Closure $generator
     */
    public function addGenerator(string $stage, $generator): void;
    /**
     * @return GeneratorInterface[]
     * @throws BadGeneratorDeclarationException
     */
    public function getGenerators(): array;
}
