<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Closure;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;

use Yiisoft\Factory\Exception\NotFoundException;
use function is_string;

final class RepositoryContainer implements ContainerInterface
{
    private ContainerInterface $rootContainer;
    private ORMInterface $orm;
    private array $roles = [];
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
            return $this->instances[$id] = $this->makeRepository($this->roles[$id]);
        }

        throw new NotFoundException("Repository $id doesn't exist.");
    }

    public function has($id): bool
    {
        if (!is_subclass_of($id, RepositoryInterface::class)) {
            return false;
        }

        if (!$this->build) {
            $this->makeRepositoryList();
            $this->build = true;
        }

        return isset($this->roles[$id]);
    }

    private function makeRepositoryList(): void
    {
        /** @var ORMInterface */
        $this->orm = $this->rootContainer->get(ORMInterface::class);
        $schema = $this->orm->getSchema();
        $roles = [];
        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, SchemaInterface::REPOSITORY);
            if (is_string($repository)) {
                $roles[$repository][] = $role;
            }
        }
        foreach ($roles as $repo => $role) {
            if (count($role) === 1) {
                $this->roles[$repo] = current($role);
            }
        }
    }

    /**
     * @psalm-pure
     * @param ORMInterface $orm
     * @param string $role
     * @return Closure
     */
    private function makeRepository(string $role): RepositoryInterface
    {
        return $this->orm->getRepository($role);
    }
}
