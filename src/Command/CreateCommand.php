<?php
namespace Yiisoft\Yii\Cycle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;

class CreateCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/create';

    public function configure(): void
    {
        $this->setDescription('Create an empty migration')
             ->setHelp('This command allows you to create a custom migration')
             ->addArgument('name', InputArgument::REQUIRED, 'Migration name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $customName = $input->getArgument('name');

        $this->createEmptyMigration($output, $customName);

        return ExitCode::OK;
    }
}
