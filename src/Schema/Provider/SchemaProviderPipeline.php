<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Psr\Container\ContainerInterface;
use RuntimeException;
use SplDoublyLinkedList;
use Yiisoft\Yii\Cycle\Exception\CumulativeException;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

/**
 * A class for working with a group of schema providers.
 * When the schema is read, it queues the specified schema providers using the {@see DeferredSchemaProviderDecorator}.
 */
final class SchemaProviderPipeline implements SchemaProviderInterface
{
    /** @var null|SplDoublyLinkedList<int, DeferredSchemaProviderDecorator> */
    private ?SplDoublyLinkedList $providers = null;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function withConfig(array $config): self
    {
        $new = clone $this;
        $new->providers = $this->createPipeline($new->container, $config);
        return $new;
    }

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        if ($this->providers === null) {
            throw new RuntimeException(self::class . ' is not configured.');
        }
        if ($this->providers->count() === 0) {
            return $nextProvider === null ? null : $nextProvider->read();
        }
        $this->providers->rewind();
        return $this->providers->current()->read($nextProvider);
    }

    public function clear(): bool
    {
        if ($this->providers === null) {
            throw new RuntimeException(self::class . ' is not configured.');
        }
        $exceptions = [];
        $result = true;
        foreach ($this->providers as $provider) {
            try {
                $result = $provider->clear() || $result;
            } catch (\Throwable $e) {
                $exceptions[] = $e;
            }
        }
        if (count($exceptions)) {
            throw new CumulativeException(...$exceptions);
        }
        return $result;
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
