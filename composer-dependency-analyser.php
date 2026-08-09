<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/config', isDev: false)
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // `yiisoft/definitions` is used only in `config/di*`, which are loaded by
    // consumers using `yiisoft/di`, that already requires `yiisoft/definitions` itself.
    ->ignoreErrorsOnPackageAndPath('yiisoft/definitions', __DIR__ . '/config', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    // `cycle/entity-behavior` is an optional integration used conditionally via class_exists() in config/di.php.
    ->ignoreErrorsOnPackageAndPath('cycle/entity-behavior', __DIR__ . '/config/di.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    // `spiral/files` is an optional integration used conditionally in config/di.php.
    ->ignoreErrorsOnPackageAndPath('spiral/files', __DIR__ . '/config/di.php', [ErrorType::SHADOW_DEPENDENCY])
    // jetbrains/phpstorm-attributes is IDE-only attributes
    ->ignoreErrorsOnPackage('jetbrains/phpstorm-attributes', [ErrorType::DEV_DEPENDENCY_IN_PROD]);
