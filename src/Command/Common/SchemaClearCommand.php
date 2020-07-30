<?php

namespace Yiisoft\Yii\Cycle\Command\Common;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

class SchemaClearCommand extends Command
{
    protected static $defaultName = 'cycle/schema/clear';

    private CycleDependencyProxy $promise;

    public function __construct(CycleDependencyProxy $promise)
    {
        $this->promise = $promise;
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Clear current schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->promise->getSchemaManager()->clear();
        $output->writeln('Schema cleared.');
        return ExitCode::OK;
    }
}
