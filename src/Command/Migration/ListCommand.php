<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Migration;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('migrate:list', 'Prints list of all migrations')]
final class ListCommand extends BaseMigrationCommand
{
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $list = $this->findMigrations($output);

        foreach ($list as $migration) {
            $state = $migration->getState();
            $output->writeln('<fg=cyan>' . $state->getName() . '</> '
                . '<fg=yellow>[' . (self::MIGRATION_STATUS[$state->getStatus()] ?? '?') . ']</>');
        }
        return self::SUCCESS;
    }
}
