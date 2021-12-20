<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Definitions\Exception\NotInstantiableClassException;
use Yiisoft\Di\NotFoundException;

use function is_string;

final class RepositoryContainer implements ContainerInterface
{
    private ContainerInterface $rootContainer;
    private ORMInterface $orm;

    private bool $rolesBuilt = false;
    private array $roles = [];

    private array $instances;

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

        if (!is_subclass_of($id, RepositoryInterface::class)) {
            throw new NotInstantiableClassException(
                $id,
                sprintf('Can not instantiate "%s" because it is not a subclass of "%s".', $id, RepositoryInterface::class)
            );
        }

        throw new NotFoundException($id);
    }

    public function has($id): bool
    {
        if (!is_subclass_of($id, RepositoryInterface::class)) {
            return false;
        }

        if (!$this->rolesBuilt) {
            $this->makeRepositoryList();
            $this->rolesBuilt = true;
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
        foreach ($roles as $repository => $role) {
            if (count($role) === 1) {
                $this->roles[$repository] = current($role);
            }
        }
    }

    private function makeRepository(string $role): RepositoryInterface
    {
        return $this->orm->getRepository($role);
    }
}
