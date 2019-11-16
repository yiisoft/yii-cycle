<?php
namespace Yiisoft\Yii\Cycle\Command;

use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Exception\RepositoryException;
use Spiral\Migrations\Migration;
use Spiral\Migrations\Migrator;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Cycle\Helper\CycleOrmHelper;

class CreateCommand extends Command
{
    protected static $defaultName = 'migrate/create';

    /** @var DatabaseManager */
    private $dbal;

    /** @var CycleOrmHelper */
    private $cycleHelper;

    /** @var MigrationConfig */
    private $config;

    /** @var Migrator */
    private $migrator;

    public function __construct(
        DatabaseManager $dbal,
        MigrationConfig $conf,
        CycleOrmHelper $cycleHelper,
        Migrator $migrator
    ) {
        parent::__construct();
        $this->dbal = $dbal;
        $this->config = $conf;
        $this->cycleHelper = $cycleHelper;
        $this->migrator = $migrator;
    }

    public function configure(): void
    {
        $this->setDescription('Create a migration')
             ->setHelp('This command allows you to create a custom migration')
             ->addArgument('name', InputArgument::REQUIRED, 'Migration name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $customName = $input->getArgument('name');
        $databaseName = $this->dbal->database()->getName();

        // unique class name for the migration
        $name = sprintf(
            'orm_%s_%s',
            $databaseName,
            md5(microtime(true) . microtime(false))
        );

        $class = new ClassDeclaration($name, 'Migration');
        $class->constant('DATABASE')->setProtected()->setValue($databaseName);
        $class->method('up')->setPublic();
        $class->method('down')->setPublic();

        $file = new FileDeclaration($this->config->getNamespace());
        $file->addUse(Migration::class);
        $file->addElement($class);

        $fileName = substr(sprintf('%s_%s', $databaseName, $customName), 0, 128);

        try {
            $migrationFile = $this->migrator->getRepository()
                                            ->registerMigration($fileName, $class->getName(), $file->render());
        } catch (RepositoryException $e) {
            $output->writeln('<fg=yellow>Can not create migration</>');
            $output->writeln('<fg=red>' . $e->getMessage() . '</>');
            return;
        }
        $output->writeln('<info>' . basename($migrationFile) . ' has been created in '
            . $this->config->getDirectory() . '</info>');

    }
}
