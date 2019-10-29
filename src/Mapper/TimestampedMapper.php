<?php

namespace Yiisoft\Yii\Cycle\Mapper;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Table;
use Cycle\ORM\Command\ContextCarrierInterface;
use Cycle\ORM\Command\Database\Update;
use Cycle\ORM\Heap\Node;
use Cycle\ORM\Heap\State;
use Cycle\ORM\Mapper\Mapper;

/**
 * You can use the annotated entities extension to automatically declare the needed columns from inside your mapper
 * @See https://github.com/cycle/docs/blob/master/advanced/timestamp.md#automatically-define-columns
 *
 * @Table(
 *      columns={"created_at": @Column(type="datetime"), "updated_at": @Column(type="datetime")}
 * )
 */
class TimestampedMapper extends Mapper
{
    public function queueCreate($entity, Node $node, State $state): ContextCarrierInterface
    {
        $command = parent::queueCreate($entity, $node, $state);

        $state->register('created_at', new \DateTimeImmutable(), true);
        $command->register('created_at', new \DateTimeImmutable(), true);

        $state->register('updated_at', new \DateTimeImmutable(), true);
        $command->register('updated_at', new \DateTimeImmutable(), true);

        return $command;
    }

    public function queueUpdate($entity, Node $node, State $state): ContextCarrierInterface
    {
        /** @var Update $command */
        $command = parent::queueUpdate($entity, $node, $state);

        $state->register('updated_at', new \DateTimeImmutable(), true);
        $command->registerAppendix('updated_at', new \DateTimeImmutable());

        return $command;
    }
}
