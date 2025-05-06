<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Schema;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

#[AsCommand('cycle/schema/clear', 'Clears the current schema cache')]
final class SchemaClearCommand extends Command
{
    public function __construct(private readonly CycleDependencyProxy $promise)
    {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->promise->getSchemaProvider()->clear();
        return self::SUCCESS;
    }
}
