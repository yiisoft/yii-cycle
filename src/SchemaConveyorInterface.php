<?php

namespace Yiisoft\Yii\Cycle;

use Cycle\Schema\GeneratorInterface;

interface SchemaConveyorInterface
{
    public const STAGE_INDEX = 'index';
    public const STAGE_RENDER = 'render';
    public const STAGE_USERLAND = 'userland';
    public const STAGE_POSTPROCESS = 'postprocess';

    /**
     * @param string $stage
     * @param mixed  $generator
     */
    public function addGenerator(string $stage, $generator): void;
    /**
     * @return GeneratorInterface[]
     */
    public function getConveyor(): array;
}
