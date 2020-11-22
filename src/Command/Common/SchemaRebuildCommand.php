<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Common;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

class SchemaRebuildCommand extends Command
{
    protected static $defaultName = 'cycle/schema/rebuild';
    private CycleDependencyProxy $promise;

    public function __construct(CycleDependencyProxy $promise)
    {
        $this->promise = $promise;
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Rebuild schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $provider = $this->promise->getSchemaProvider();
        $provider->clear();
        $provider->read();

        return ExitCode::OK;
    }
}
