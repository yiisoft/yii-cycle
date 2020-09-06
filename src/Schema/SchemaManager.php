<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

use Generator;
use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;
use Yiisoft\Yii\Cycle\Exception\CumulativeException;

/**
 * SchemaManager allows reading schema from providers available and clearing the schema in providers.
 */
final class SchemaManager
{
    private ContainerInterface $container;
    /** @var string[]|SchemaProviderInterface[] */
    private array $providers;

    public function __construct(ContainerInterface $container, array $providers)
    {
        $this->container = $container;
        $this->providers = $providers;
    }

    public function read(): ?array
    {
        $toWrite = new \SplStack();
        $schema = null;

        foreach ($this->getProviders() as $provider) {
            if ($provider->isReadable()) {
                $schema = $provider->read();
                if ($schema !== null) {
                    break;
                }
            }
            if ($provider->isWritable()) {
                $toWrite->push($provider);
            }
        }

        if ($schema === null) {
            return null;
        }

        // Save schema
        /** @var SchemaProviderInterface $provider */
        foreach ($toWrite as $provider) {
            $provider->write($schema);
        }

        return $schema;
    }

    public function clear(): void
    {
        $providers = iterator_to_array($this->getProviders());
        array_pop($providers);

        $exceptions = [];
        foreach ($providers as $provider) {
            if ($provider->isWritable()) {
                try {
                    $provider->clear();
                } catch (\Throwable $e) {
                    $exceptions[] = $e;
                }
            }
        }
        if (count($exceptions)) {
            throw new CumulativeException(...$exceptions);
        }
    }

    /**
     * @return Generator|SchemaProviderInterface[]
     * @throws BadDeclarationException
     */
    private function getProviders(): Generator
    {
        foreach ($this->providers as $key => &$provider) {
            // Providers resolving
            if (is_string($provider)) {
                $provider = $this->container->get($provider);
            }
            // If Provider defined as ClassName => ConfigArray
            if (is_array($provider) && is_string($key)) {
                $provider = $this->container->get($key)->withConfig($provider);
            }

            if (!$provider instanceof SchemaProviderInterface) {
                throw new BadDeclarationException('Provider', SchemaProviderInterface::class, $provider);
            }
            yield $provider;
        }
    }
}
