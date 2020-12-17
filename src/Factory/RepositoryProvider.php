<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Closure;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\SchemaInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\Support\ServiceProvider;

use function is_string;

final class RepositoryProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        /** @var ORMInterface */
        $orm = $container->get(ORMInterface::class);
        /** @psalm-suppress InaccessibleMethod */
        $container->setMultiple($this->getRepositoryFactories($orm));
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
