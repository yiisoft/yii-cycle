<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Migration;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;

final class ListCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/list';

    public function configure(): void
    {
        $this
            ->setDescription('Print list of all migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $list = $this->findMigrations($output);

        foreach ($list as $migration) {
            $state = $migration->getState();
            $output->writeln('<fg=cyan>' . $state->getName() . '</> '
                . '<fg=yellow>[' . (static::MIGRATION_STATUS[$state->getStatus()] ?? '?') . ']</>');
        }
        return ExitCode::OK;
    }
}
