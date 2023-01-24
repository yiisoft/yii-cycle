<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\Database\DatabaseManager;
use Cycle\ORM\Collection\CollectionFactoryInterface;
use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use RuntimeException;
use Spiral\Core\FactoryInterface as SpiralFactoryInterface;
use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;
use Yiisoft\Yii\Cycle\Exception\ConfigException;

use function is_string;

/**
 * The factory for the ORM Factory {@see FactoryInterface}.
 *
 * @psalm-type CollectionsConfig = array{
 *     default?: string|null,
 *     factories?: array<non-empty-string, class-string<CollectionFactoryInterface>>
 * }
 */
final class OrmFactory
{
    /** @var CollectionsConfig */
    private array $collectionsConfig;

    /**
     * @param CollectionsConfig $collectionsConfig
     */
    public function __construct(array $collectionsConfig)
    {
        $this->collectionsConfig = $collectionsConfig;
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
            foreach ($this->collectionsConfig['factories'] ?? [] as $alias => $definition) {
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
            $default = $this->collectionsConfig['default'] ?? null;
            if ($default !== null) {
                if (!\array_key_exists($default, $factories)) {
                    if (!\is_a($default, CollectionFactoryInterface::class, true)) {
                        $cfgPath[] = 'default';
                        throw new RuntimeException(\sprintf('Default collection factory `%s` not found.', $default));
                    }
                    $default = is_string($default) ? $injector->make($default) : $default;
                } else {
                    $default = $factories[$default];
                }
            }
        } catch (\Throwable $e) {
            throw new ConfigException($cfgPath, $e->getMessage(), 0, $e);
        }

        $result = new Factory($dbal, null, $factory, $default);
        // attach collection factories
        foreach ($factories as $alias => $collectionFactory) {
            $result = $result->withCollectionFactory($alias, $collectionFactory);
        }
        return $result;
    }
}
