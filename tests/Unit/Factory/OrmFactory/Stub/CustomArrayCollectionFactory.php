<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Factory\OrmFactory\Stub;

use Cycle\ORM\Collection\CollectionFactoryInterface;
use Cycle\ORM\Exception\CollectionFactoryException;
use Yiisoft\Yii\Cycle\Factory\OrmFactory;

/**
 * @psalm-import-type CollectionsConfig from OrmFactory
 */
final class CustomArrayCollectionFactory implements CollectionFactoryInterface
{
    public function getInterface(): ?string
    {
        return null;
    }

    /**
     * @psalm-param string $class
     */
    public function withCollectionClass(string $class): static
    {
        return $this;
    }

    public function collect(iterable $data): array
    {
        return match (true) {
            \is_array($data) => $data,
            $data instanceof \Traversable => \iterator_to_array($data),
            default => throw new CollectionFactoryException('Unsupported iterable type.'),
        };
    }
}
