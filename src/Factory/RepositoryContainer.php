<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\SchemaInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Cycle\Exception\NotFoundException;
use Yiisoft\Yii\Cycle\Exception\NotInstantiableClassException;

use function is_string;

final class RepositoryContainer implements ContainerInterface
{
    private ContainerInterface $rootContainer;
    private ORMInterface $orm;

    private bool $rolesBuilt = false;
    private array $roles = [];

    private array $instances = [];

    public function __construct(ContainerInterface $rootContainer)
    {
        $this->rootContainer = $rootContainer;
        $this->orm = $rootContainer->get(ORMInterface::class);
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
            throw new NotInstantiableClassException($id);
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

    /**
     * @psalm-param class-string $role
     */
    private function makeRepository(string $role): RepositoryInterface
    {
        return $this->orm->getRepository($role);
    }
}
