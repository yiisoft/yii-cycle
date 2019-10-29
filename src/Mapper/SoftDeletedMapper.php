<?php

namespace Yiisoft\Yii\Cycle\Mapper;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Table;
use Cycle\ORM\Command\CommandInterface;
use Cycle\ORM\Command\Database\Update;
use Cycle\ORM\Context\ConsumerInterface;
use Cycle\ORM\Heap\Node;
use Cycle\ORM\Heap\State;
use Cycle\ORM\Mapper\Mapper;

/**
 * You can use the annotated entities extension to automatically declare the needed columns from inside your mapper
 * @See https://github.com/cycle/docs/blob/master/advanced/soft-deletes.md
 *
 * @Table(
 *      columns={"deleted_at": @Column(type="datetime", nullable=true)}
 * )
 */
class SoftDeletedMapper extends Mapper
{
    public function queueDelete($entity, Node $node, State $state): CommandInterface
    {
        // identify entity as being "deleted"
        $state->setStatus(Node::SCHEDULED_DELETE);
        $state->decClaim();

        $command = new Update(
            $this->source->getDatabase(),
            $this->source->getTable(),
            ['deleted_at' => new \DateTimeImmutable()]
        );

        // forward primaryKey value from entity state
        // this sequence is only required if the entity is created and deleted
        // within one transaction
        $command->waitScope($this->primaryColumn);
        $state->forward(
            $this->primaryKey,
            $command,
            $this->primaryColumn,
            true,
            ConsumerInterface::SCOPE
        );

        return $command;
    }
}
