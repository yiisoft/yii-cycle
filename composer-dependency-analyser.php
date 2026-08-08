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
    // jetbrains/phpstorm-attributes: IDE-only attributes, previously whitelisted the same way in
    // composer-require-checker.json.
    // cycle/entity-behavior: optional integration used conditionally via class_exists() in config/di.php.
    // yiisoft/definitions: Reference/DynamicReference used in config/di.php; the DI container that consumes
    // this config (e.g. yiisoft/di) brings yiisoft/definitions transitively.
    ->ignoreErrorsOnPackages(
        ['jetbrains/phpstorm-attributes', 'cycle/entity-behavior', 'yiisoft/definitions'],
        [ErrorType::DEV_DEPENDENCY_IN_PROD],
    );
