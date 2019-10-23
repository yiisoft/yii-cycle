<?php
namespace Yiisoft\Yii\Cycle\Command;

use Spiral\Migrations\Migrator;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;

class CreateCommand extends Command
{
    protected static $defaultName = 'migrate/create';

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
        $this->setDescription('Create a migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
    }
}
