<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

use Psr\Container\ContainerInterface;

final class SchemaProviderDispatcher
{
    private ContainerInterface $container;
    /** @var string[]|SchemaProviderInterface[] */
    private array $providers = [];

    public function __construct(ContainerInterface $container, array $providers)
    {
        $this->container = $container;
        $this->providers = $providers;
    }

    public function readSchema(): ?array
    {
        $toWrite = new \SplStack();
        $schema = null;

        $this->walkProviders(static function (SchemaProviderInterface $provider) use (&$schema, $toWrite): bool {
            // Try to read schema
            if ($provider->isReadable()) {
                $schema = $provider->read();
                if ($schema !== null) {
                    return false;
                }
            }
            if ($provider->isWritable()) {
                $toWrite->push($provider);
            }
            return true;
        });

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

    public function clearSchema(): void
    {
        /** @var SchemaProviderInterface[] $toClear */
        $toClear = [];
        $isWritableLast = false;
        $this->walkProviders(static function (SchemaProviderInterface $provider) use (&$toClear, &$isWritableLast) {
            $isWritableLast = $provider->isWritable();
            if ($isWritableLast) {
                $toClear[] = $provider;
            }
        });
        if ($isWritableLast) {
            array_pop($toClear);
        }
        foreach ($toClear as $provider) {
            $provider->clear();
        }
    }

    private function walkProviders(\Closure $closure)
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
                throw new \RuntimeException('Provider should be instance of SchemaProviderInterface.');
            }
            if ($closure($provider) === false) {
                break;
            }
        }
    }
}
