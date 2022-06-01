<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Schema;

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
        $this->setDescription('Clear current schema cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->promise
            ->getSchemaProvider()
            ->clear();
        return ExitCode::OK;
    }
}
