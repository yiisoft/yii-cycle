<?php
namespace Yiisoft\Yii\Cycle\Command;

use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\MigrationInterface;
use Spiral\Migrations\Migrator;
use Spiral\Migrations\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;

class UpCommand extends Command
{
    protected static $defaultName = 'migrate/up';

    /** @var MigrationConfig */
    private $config;

    /** @var Migrator */
    private $migrator;

    /** @var CycleOrmHelper */
    private $cycleOrmHelper;

    /**
     * MigrateGenerateCommand constructor.
     * @param Migrator        $migrator
     * @param MigrationConfig $conf
     * @param CycleOrmHelper  $cycleOrmHelper
     */
    public function __construct(Migrator $migrator, MigrationConfig $conf, CycleOrmHelper $cycleOrmHelper)
    {
        parent::__construct();
        $this->config = $conf;
        $this->migrator = $migrator;
        $this->cycleOrmHelper = $cycleOrmHelper;
    }

    public function configure(): void
    {
        $this
            ->setDescription('Execute all new migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // drop cached schema
        $this->cycleOrmHelper->dropCurrentSchemaCache();

        $list = $this->migrator->getMigrations();
        $output->writeln('<info>' . count($list) . ' migrations found in ' . $this->config->getDirectory() . '</info>');

        $limit = PHP_INT_MAX;
        $statuses = [
            State::STATUS_UNDEFINED => 'undefined',
            State::STATUS_PENDING => 'pending',
            State::STATUS_EXECUTED => 'executed',
        ];
        try {
            do {
                $migration = $this->migrator->run();
                if (!$migration instanceof MigrationInterface) {
                    break;
                }

                $state = $migration->getState();
                $status = $state->getStatus();
                $output->writeln($state->getName() . ': ' . ($statuses[$status] ?? $status));
            } while (--$limit > 0);
        } catch (\Throwable $e) {
            $output->writeln([
                '<fg=red>Error!</fg=red>',
                $e->getMessage(),
            ]);
            return;
        }
    }
}
