<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Schema;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

#[AsCommand('cycle/schema/rebuild', 'Rebuilds the database schema')]
final class SchemaRebuildCommand extends Command
{
    public function __construct(private readonly CycleDependencyProxy $promise)
    {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $provider = $this->promise->getSchemaProvider();
        $provider->clear();
        $provider->read();

        return self::SUCCESS;
    }
}
