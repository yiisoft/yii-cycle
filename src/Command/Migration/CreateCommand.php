<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Migration;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/create';
    protected static $defaultDescription = 'Creates an empty migration';

    protected function configure(): void
    {
        $this->setHelp('This command allows you to create a custom migration')
             ->addArgument('name', InputArgument::REQUIRED, 'Migration name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $customName */
        $customName = $input->getArgument('name');

        $this->createEmptyMigration($output, $customName);

        return self::SUCCESS;
    }
}
