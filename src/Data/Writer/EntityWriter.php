<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Writer;

use Cycle\ORM\EntityManagerInterface;
use Throwable;
use Yiisoft\Data\Writer\DataWriterInterface;

final class EntityWriter implements DataWriterInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws Throwable
     */
    public function write(iterable $items): void
    {
        foreach ($items as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->run();
    }

    public function delete(iterable $items): void
    {
        foreach ($items as $entity) {
            $this->entityManager->delete($entity);
        }
        $this->entityManager->run();
    }
}
