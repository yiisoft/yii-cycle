<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Migration;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/list';
    protected static $defaultDescription = 'Prints list of all migrations';

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
