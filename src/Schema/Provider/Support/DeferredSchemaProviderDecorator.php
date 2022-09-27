<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider\Support;

use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

/**
 * Auxiliary class for building scheme providers in a pipeline.
 */
final class DeferredSchemaProviderDecorator implements SchemaProviderInterface
{
    private array $config = [];
    private ?SchemaProviderInterface $latestProvider = null;
    private bool $resolved = false;

    /**
     * @param $provider
     */
    public function __construct(private ContainerInterface $container, private SchemaProviderInterface|string $provider, private ?\Yiisoft\Yii\Cycle\Schema\Provider\Support\DeferredSchemaProviderDecorator $nextProvider)
    {
    }

    public function withConfig(array $config): self
    {
        $provider = !$this->resolved && count($this->config) === 0 ? $this->provider : $this->getProvider();
        $new = new self($this->container, $provider, $this->nextProvider);
        $new->config = $config;
        return $new;
    }

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        $nextProvider ??= $this->latestProvider;
        if ($nextProvider !== null && $this->nextProvider !== null) {
            $nextProvider = $this->nextProvider->withLatestProvider($nextProvider);
        } else {
            $nextProvider = $this->nextProvider ?? $nextProvider;
        }
        return $this->getProvider()->read($nextProvider);
    }

    public function clear(): bool
    {
        return $this->getProvider()->clear();
    }

    /**
     * @psalm-suppress InvalidReturnType,InvalidReturnStatement
     */
    private function getProvider(): SchemaProviderInterface
    {
        if ($this->resolved) {
            return $this->provider;
        }
        $provider = $this->provider;
        if (is_string($provider)) {
            $provider = $this->container->get($provider);
        }
        if (!$provider instanceof SchemaProviderInterface) {
            throw new BadDeclarationException('Provider', SchemaProviderInterface::class, $provider);
        }
        $this->provider = count($this->config) > 0 ? $provider->withConfig($this->config) : $provider;
        $this->resolved = true;
        return $this->provider;
    }

    private function withLatestProvider(SchemaProviderInterface $provider): self
    {
        // resolve provider
        $this->getProvider();
        $new = clone $this;
        $new->latestProvider = $provider;
        return $new;
    }
}
