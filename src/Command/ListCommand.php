<?php
namespace Yiisoft\Yii\Cycle\Command;

use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Migrator;
use Spiral\Migrations\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    protected static $defaultName = 'migrate/list';

    /** @var MigrationConfig */
    private $config;
    /** @var Migrator */
    private $migrator;
    /**
     * MigrateGenerateCommand constructor.
     * @param Migrator                 $migrator
     * @param MigrationConfig   $conf
     */
    public function __construct(Migrator $migrator, MigrationConfig $conf)
    {
        $this->config = $conf;
        $this->migrator = $migrator;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Print list of all migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list = $this->migrator->getMigrations();
        $output->writeln('<info>' . count($list) . ' migrations found in ' . $this->config->getDirectory() . '</info>');

        $statuses = [
            State::STATUS_UNDEFINED => 'undefined',
            State::STATUS_PENDING => 'pending',
            State::STATUS_EXECUTED => 'executed',
        ];
        $list = $this->migrator->getMigrations();

        foreach ($list as $migration) {
            $state = $migration->getState();
            $output->writeln($state->getName() . ' <fg=yellow>[' . ($statuses[$state->getStatus()] ?? '?') . ']</fg=yellow>');
        }
    }
}
