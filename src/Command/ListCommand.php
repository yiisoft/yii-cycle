<?php
namespace Yiisoft\Yii\Cycle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;

class ListCommand extends BaseMigrationCommand
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
                . '<fg=yellow>[' . (static::$migrationStatus[$state->getStatus()] ?? '?') . ']</>');
        }
        return ExitCode::OK;
    }
}
