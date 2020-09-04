<?php

namespace PHPSTORM_META {

    expectedArguments(
        \Yiisoft\Yii\Cycle\Schema\Conveyor\AnnotatedSchemaConveyor::setTableNaming(),
        0,
        argumentsSet('\Cycle\Annotated\Entities::TABLE_NAMINGS'),
    );
    expectedArguments(
        \Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface::addGenerator(),
        0,
        argumentsSet('\Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface::STAGES'),
    );

    registerArgumentsSet(
        '\Cycle\Annotated\Entities::TABLE_NAMINGS',
        \Cycle\Annotated\Entities::TABLE_NAMING_PLURAL,
        \Cycle\Annotated\Entities::TABLE_NAMING_SINGULAR,
        \Cycle\Annotated\Entities::TABLE_NAMING_NONE,
    );
    registerArgumentsSet(
        '\Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface::STAGES',
        \Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface::STAGE_INDEX,
        \Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface::STAGE_RENDER,
        \Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface::STAGE_USERLAND,
        \Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface::STAGE_POSTPROCESS,
    );
}
