<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Schema;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

final class SchemaClearCommand extends Command
{
    protected static $defaultName = 'cycle/schema/clear';
    protected static $defaultDescription = 'Clears the current schema cache';

    public function __construct(private CycleDependencyProxy $promise)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->promise->getSchemaProvider()->clear();
        return ExitCode::OK;
    }
}
