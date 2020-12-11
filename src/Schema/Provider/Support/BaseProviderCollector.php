<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider\Support;

use Psr\Container\ContainerInterface;
use RuntimeException;
use SplFixedArray;
use Yiisoft\Yii\Cycle\Exception\CumulativeException;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

abstract class BaseProviderCollector implements SchemaProviderInterface
{
    protected const IS_SEQUENCE_PIPELINE = true;

    /** @var SplFixedArray<DeferredSchemaProviderDecorator>|null */
    protected ?SplFixedArray $providers = null;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return $this
     *
     * @psalm-immutable
     */
    public function withConfig(array $config): self
    {
        $new = clone $this;
        $new->providers = $this->createSequence($new->container, $config);
        return $new;
    }

    public function clear(): bool
    {
        if ($this->providers === null) {
            throw new RuntimeException(self::class . ' is not configured.');
        }
        $exceptions = [];
        $result = false;
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

    protected function createSequence(ContainerInterface $container, array $providers): SplFixedArray
    {
        $size = count($providers);
        $stack = new SplFixedArray($size);
        $nextProvider = null;
        foreach (array_reverse($providers) as $key => $definition) {
            $config = [];
            if (is_array($definition)) {
                if (is_string($key)) {
                    $config = $definition;
                    $definition = $key;
                } else {
                    $config = $definition[1] ?? [];
                    $definition = $definition[0];
                }
            }
            $nextProvider = (new DeferredSchemaProviderDecorator(
                $container,
                $definition,
                static::IS_SEQUENCE_PIPELINE ? $nextProvider : null
            ))->withConfig($config);
            $stack[--$size] = $nextProvider;
        }
        return $stack;
    }
}
