<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema;

use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Schema\Provider\ConveyorSchemaProvider;
use Yiisoft\Yii\Cycle\Schema\Provider\FromFileSchemaProvider;
use Yiisoft\Yii\Cycle\Schema\Provider\SimpleCacheSchemaProvider;

final class SchemaProviderDispatcher
{
    private ContainerInterface $container;
    /** @var string[] */
    private array $providers = [
        // SimpleCacheSchemaProvider::class,
        // ConveyorSchemaProvider::class,
        // FromFileSchemaProvider::class,
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSchemaArray(): ?array
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

    private function walkProviders(\Closure $closure)
    {
        foreach ($this->providers as &$provider) {
            if (is_string($provider)) {
                $provider = $this->container->get($provider);
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
