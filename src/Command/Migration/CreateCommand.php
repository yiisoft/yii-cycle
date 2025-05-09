<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Migration;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('migrate:create', 'Creates an empty migration')]
final class CreateCommand extends BaseMigrationCommand
{
    #[\Override]
    protected function configure(): void
    {
        $this->setHelp('This command allows you to create a custom migration')
             ->addArgument('name', InputArgument::REQUIRED, 'Migration name');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $customName */
        $customName = $input->getArgument('name');

        $this->createEmptyMigration($output, $customName);

        return self::SUCCESS;
    }
}
