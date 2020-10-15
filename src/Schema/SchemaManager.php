<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

use Psr\Container\ContainerInterface;
use SplDoublyLinkedList;
use Yiisoft\Yii\Cycle\Exception\CumulativeException;
use Yiisoft\Yii\Cycle\Schema\Provider\DeferredSchemaProviderDecorator;

/**
 * SchemaManager allows reading schema from providers available and clearing the schema in providers.
 */
final class SchemaManager
{
    /** @var SplDoublyLinkedList<int|SchemaProviderInterface> */
    private SplDoublyLinkedList $providers;

    public function __construct(ContainerInterface $container, array $providers)
    {
        $this->providers = $this->createPipeline($container, $providers);
    }

    public function read(): ?array
    {
        if ($this->providers->count() === 0) {
            return null;
        }
        $this->providers->rewind();
        return $this->providers->current()->read();
    }

    public function clear(): void
    {
        $exceptions = [];
        foreach ($this->providers as $provider) {
            try {
                $provider->clear();
            } catch (\Throwable $e) {
                $exceptions[] = $e;
            }
        }
        if (count($exceptions)) {
            throw new CumulativeException(...$exceptions);
        }
    }

    private function createPipeline(ContainerInterface $container, array $providers): SplDoublyLinkedList
    {
        $stack = new SplDoublyLinkedList();
        $nextProvider = null;
        foreach (array_reverse($providers) as $key => $definition) {
            $config = [];
            if (is_string($key) && is_array($definition)) {
                $config = $definition;
                $definition = $key;
            }
            $nextProvider = (new DeferredSchemaProviderDecorator($container, $definition, $nextProvider))
                ->withConfig($config);
            $stack->unshift($nextProvider);
        }
        return $stack;
    }
}
