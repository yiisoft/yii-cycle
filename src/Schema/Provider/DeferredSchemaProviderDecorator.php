<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider;

use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Exception\BadDeclarationException;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;

final class DeferredSchemaProviderDecorator implements SchemaProviderInterface
{
    /** @var SchemaProviderInterface|string */
    private $provider;
    private array $config = [];
    private ?SchemaProviderInterface $nextProvider;
    private bool $resolved = false;
    private ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     * @param $provider
     * @param null|SchemaProviderInterface $nextProvider
     */
    public function __construct(ContainerInterface $container, $provider, ?SchemaProviderInterface $nextProvider)
    {
        $this->provider = $provider;
        $this->container = $container;
        $this->nextProvider = $nextProvider;
    }
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
    public function withConfig(array $config): SchemaProviderInterface
    {
        $provider = !$this->resolved && count($this->config) === 0 ? $this->provider : $this->getProvider();
        $new = new self($this->container, $provider, $this->nextProvider);
        $new->config = $config;
        return $new;
    }
    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        return $this->getProvider()->read($this->nextProvider);
    }
    public function clear(): bool
    {
        return $this->getProvider()->clear();
    }
}
