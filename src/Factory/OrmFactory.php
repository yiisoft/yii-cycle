<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\Database\DatabaseManager;
use Cycle\ORM\Collection\CollectionFactoryInterface;
use Cycle\ORM\FactoryInterface;
use Spiral\Core\FactoryInterface as SpiralFactoryInterface;
use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;
use Yiisoft\Yii\Cycle\Exception\ConfigException;

final class OrmFactory
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @throws ConfigException
     */
    public function __invoke(
        DatabaseManager $dbal,
        SpiralFactoryInterface $factory,
        Injector $injector,
    ): FactoryInterface {
        // Manage collection factory list
        $cfgPath = ['yiisoft/yii-cycle', 'collections'];

        try {
            // Resolve collection factories
            $factories = [];
            foreach ($this->config['factories'] ?? [] as $alias => $definition) {
                $factories[$alias] = $injector->make($definition);
                if (!$factories[$alias] instanceof CollectionFactoryInterface) {
                    $cfgPath[] = 'factories';
                    throw new BadDeclarationException(
                        "Collection factory `$alias`",
                        CollectionFactoryInterface::class,
                        $factories[$alias]
                    );
                }
            }

            // Resolve default collection factory
            $default = $this->config['default'] ?? null;
            if ($default !== null) {
                if (!\array_key_exists($default, $factories)) {
                    if (!\is_a($default, CollectionFactoryInterface::class, true)) {
                        $cfgPath[] = 'default';
                        throw new \RuntimeException(\sprintf('Default collection factory `%s` not found.', $default));
                    }
                    $default = \is_string($default) ? $injector->make($default) : $default;
                } else {
                    $default = $factories[$default];
                }
            }
        } catch (\Throwable $e) {
            throw new ConfigException($cfgPath, $e->getMessage(), 0, $e);
        }

        $result = new \Cycle\ORM\Factory($dbal, null, $factory, $default);
        // attach collection factories
        foreach ($factories as $alias => $collectionFactory) {
            $result = $result->withCollectionFactory($alias, $collectionFactory);
        }
        return $result;
    }
}
