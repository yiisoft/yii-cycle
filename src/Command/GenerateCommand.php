<?php
namespace Yiisoft\Yii\Cycle\Command;

use Spiral\Migrations\Migrator;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Cycle\Generator\ShowChangesGenerator;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;

class GenerateCommand extends Command
{
    protected static $defaultName = 'migrate/generate';

    /** @var Migrator */
    private $migrator;

    /** @var CycleOrmHelper */
    private $cycleHelper;

    /** @var MigrationConfig */
    private $config;

    public function __construct(
        Migrator $migrator,
        MigrationConfig $conf,
        CycleOrmHelper $cycleHelper
    ) {
        parent::__construct();
        $this->migrator = $migrator;
        $this->config = $conf;
        $this->cycleHelper = $cycleHelper;
    }

    public function configure(): void
    {
        $this->setDescription('Generates a migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        // check existing unapplied migrations
        $listAfter = $this->migrator->getMigrations();
        foreach ($listAfter as $migration) {
            if ($migration->getState()->getStatus() !== State::STATUS_EXECUTED) {
                $output->writeln('<fg=red>Outstanding migrations found, run `migrate/up` first.</>');
                return;
            }
        }
        // run generator
        $this->cycleHelper->generateMigrations($this->migrator, $this->config, [
            new ShowChangesGenerator($output),
        ]);

        $listBefore = $this->migrator->getMigrations();
        $added = count($listBefore) - count($listAfter);
        $output->writeln("<info>Added {$added} file(s)</info>");

        // print added migrations
        if ($added > 0) {
            foreach ($listBefore as $migration) {
                if ($migration->getState()->getStatus() !== State::STATUS_EXECUTED) {
                    $output->writeln($migration->getState()->getName());
                }
            }
        } else {
            $output->write('<info>If you want to create empty migration, use <fg=yellow>migrate/create</></info>');

            // if ($input->isInteractive() && $input instanceof StreamableInputInterface) {
            //     $output->write('Would you like to create empty migration? (Y/n): ');
            //     $answer = fgets($input->getStream() ?? STDIN);
            //     if (in_array(strtolower(trim($answer)), ['yes', 'y'])) {
            //         // create empty migration
            //         $this->cycleHelper->generateEmptyMigration($this->migrator, $this->config);
            //     }
            // }
        }
    }
}
