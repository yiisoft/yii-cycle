<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Common;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Command\Common\SchemaPhpCommand;

class SchemaRebuildCommand extends Command
{
    protected static $defaultName = 'cycle/schema/rebuild';

    private CycleDependencyProxy $promise;

    private ?ContainerInterface $container = null;

    public function __construct(CycleDependencyProxy $promise, ContainerInterface $container)
    {
        $this->promise = $promise;
        $this->container = $container;
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Rebuild schema')
            ->addArgument('file', InputArgument::OPTIONAL, 'file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->promise->getSchemaProvider()->clear();
        $schemaPhpCommand = $this->container->get(SchemaPhpCommand::class);
        $schemaPhpCommand->execute($input, $output);

        return ExitCode::OK;
    }
}
