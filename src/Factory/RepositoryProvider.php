<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Closure;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\CompositeContainer;
use Yiisoft\Di\Contracts\ServiceProviderInterface;

use function is_string;

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
                /** @var ORMInterface */
                $orm = $extended->get(ORMInterface::class);
                /** @psalm-suppress InaccessibleMethod */
                $repositoryContainer = new Container($this->getRepositoryFactories($orm));
                $compositeContainer = new CompositeContainer();
                $compositeContainer->attach($repositoryContainer);
                $compositeContainer->attach($extended);

                return $compositeContainer;
            },
        ];
    }

    /**
     * @return array<string, Closure> Repository name as key and factory as value
     */
    private function getRepositoryFactories(ORMInterface $orm): array
    {
        $schema = $orm->getSchema();
        $result = [];
        $roles = [];
        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, SchemaInterface::REPOSITORY);
            if (is_string($repository)) {
                $roles[$repository][] = $role;
            }
        }
        foreach ($roles as $repo => $role) {
            if (count($role) === 1) {
                $result[$repo] = $this->makeRepositoryFactory($orm, current($role));
            }
        }
        return $result;
    }

    /**
     * @psalm-pure
     */
    private function makeRepositoryFactory(ORMInterface $orm, string $role): Closure
    {
        return static fn (): RepositoryInterface => $orm->getRepository($role);
    }
}
