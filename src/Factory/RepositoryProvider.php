<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Closure;
use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Di\CompositeContainer;
use Yiisoft\Di\Contracts\ServiceProviderInterface;

/**
 * This provider provides factories to the container for creating Cycle entity repositories.
 * Repository list is compiled based on data from the database schema.
 */
final class RepositoryProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [];
    }

    /**
     * @return array<Closure>
     */
    public function getExtensions(): array
    {
        return [
            ContainerInterface::class => function (ContainerInterface $container, ContainerInterface $extended) {
                /** @psalm-suppress InaccessibleMethod */
                $repositoryContainer = new RepositoryContainer($extended);
                $compositeContainer = new CompositeContainer();
                $compositeContainer->attach($repositoryContainer);
                $compositeContainer->attach($extended);

                return $compositeContainer;
            },
        ];
    }
}
