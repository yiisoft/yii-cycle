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

use Yiisoft\Factory\Exception\NotFoundException;
use function is_string;

final class RepositoryContainer implements ContainerInterface
{
    private ContainerInterface $rootContainer;
    private array $repositoryFactories = [];
    private array $instances;
    private bool $build = false;

    public function __construct(ContainerInterface $rootContainer)
    {
        $this->rootContainer = $rootContainer;
    }


    public function get($id)
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if ($this->has($id)) {
            return $this->instances[$id] = $this->repositoryFactories[$id]();
        }

        throw new NotFoundException("Repository $id doesn't exist.");
    }

    public function has($id): bool
    {
        if (!is_subclass_of($id, RepositoryInterface::class)) {
            return false;
        }

        if (!$this->build) {
            $this->makeRepositoryFactories();
            $this->build = true;
        }

        return isset($this->repositoryFactories[$id]);
    }

    private function makeRepositoryFactories(): void
    {
        /** @var ORMInterface */
        $orm = $this->rootContainer->get(ORMInterface::class);
        $schema = $orm->getSchema();
        $roles = [];
        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, SchemaInterface::REPOSITORY);
            if (is_string($repository)) {
                $roles[$repository][] = $role;
            }
        }
        foreach ($roles as $repo => $role) {
            if (count($role) === 1) {
                $this->repositoryFactories[$repo] = $this->makeRepositoryFactory($orm, current($role));
            }
        }
    }

    /**
     * @psalm-pure
     * @param ORMInterface $orm
     * @param string $role
     * @return Closure
     */
    private function makeRepositoryFactory(ORMInterface $orm, string $role): Closure
    {
        return static fn (): RepositoryInterface => $orm->getRepository($role);
    }
}
