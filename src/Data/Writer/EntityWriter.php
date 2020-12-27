<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Data\Writer;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Transaction;
use Throwable;
use Yiisoft\Data\Writer\DataWriterInterface;

final class EntityWriter implements DataWriterInterface
{
    private ORMInterface $orm;

    public function __construct(ORMInterface $orm) {
        $this->orm = $orm;
    }

    /**
     * @throws Throwable
     */
    public function write(iterable $items): void
    {
        $transaction = new Transaction($this->orm);
        foreach ($items as $entity) {
            $transaction->persist($entity);
        }
        $transaction->run();
    }
}
