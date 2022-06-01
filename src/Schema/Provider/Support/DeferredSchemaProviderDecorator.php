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
    /** @var SchemaProviderInterface|string */
    private $provider;
    private array $config = [];
    private ?self $nextProvider;
    private ?SchemaProviderInterface $latestProvider = null;
    private bool $resolved = false;
    private ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     * @param $provider
     * @param self|null $nextProvider
     */
    public function __construct(ContainerInterface $container, $provider, ?self $nextProvider)
    {
        $this->provider = $provider;
        $this->container = $container;
        $this->nextProvider = $nextProvider;
    }

    public function withConfig(array $config): self
    {
        $provider = !$this->resolved && count($this->config) === 0 ? $this->provider : $this->getProvider();
        $new = new self($this->container, $provider, $this->nextProvider);
        $new->config = $config;
        return $new;
    }

    public function read(?SchemaProviderInterface $latestProvider = null): ?array
    {
        $latestProvider ??= $this->latestProvider;
        if ($latestProvider !== null && $this->nextProvider !== null) {
            $nextProvider = $this->nextProvider->withLatestProvider($latestProvider);
        } else {
            $nextProvider = $this->nextProvider ?? $latestProvider;
        }
        return $this
            ->getProvider()
            ->read($nextProvider);
    }

    public function clear(): bool
    {
        return $this
            ->getProvider()
            ->clear();
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
