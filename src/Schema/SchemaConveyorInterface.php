<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

use Closure;
use Cycle\Schema\GeneratorInterface;
use Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException;

interface SchemaConveyorInterface
{
    /**
     * Declare entities and their fields
     * @psalm-suppress MissingClassConstType
     */
    public const STAGE_INDEX = 'index';

    /**
     * Render tables and relations
     * @psalm-suppress MissingClassConstType
     */
    public const STAGE_RENDER = 'render';

    /**
     * Userland scripts
     * @psalm-suppress MissingClassConstType
     */
    public const STAGE_USERLAND = 'userland';

    /**
     * Post processing
     * @psalm-suppress MissingClassConstType
     */
    public const STAGE_POSTPROCESS = 'postprocess';

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
