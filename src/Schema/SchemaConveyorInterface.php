<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

use Closure;
use Cycle\Schema\GeneratorInterface;
use Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException;

interface SchemaConveyorInterface
{
    // declare entities and their fields
    public const string STAGE_INDEX = 'index';
    // render tables and relations
    public const string STAGE_RENDER = 'render';
    // userland scripts
    public const string STAGE_USERLAND = 'userland';
    // post processing
    public const string STAGE_POSTPROCESS = 'postprocess';

    /**
     * @param self::STAGE_* $stage
     * @param Closure|GeneratorInterface|string $generator
     */
    public function addGenerator(string $stage, mixed $generator): void;

    /**
     * @throws BadGeneratorDeclarationException
     *
     * @return GeneratorInterface[]
     */
    public function getGenerators(): array;
}
