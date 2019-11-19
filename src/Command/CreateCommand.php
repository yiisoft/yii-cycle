<?php
namespace Yiisoft\Yii\Cycle\Command;

use Cycle\Migrations\MigrationImage;
use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Exception\RepositoryException;
use Spiral\Migrations\Migrator;
use Spiral\Migrations\Config\MigrationConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{
    protected static $defaultName = 'migrate/create';

    /** @var DatabaseManager */
    private $dbal;

    /** @var MigrationConfig */
    private $config;

    /** @var Migrator */
    private $migrator;

    public function __construct(
        DatabaseManager $dbal,
        MigrationConfig $conf,
        Migrator $migrator
    ) {
        parent::__construct();
        $this->dbal = $dbal;
        $this->config = $conf;
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
        // get default database
        $databaseName = $this->dbal->database()->getName();

        $migrationSkeleton = new MigrationImage($this->config, $databaseName);
        $migrationSkeleton->setName($customName);

        try {
            $migrationFile = $this->migrator->getRepository()->registerMigration(
                $migrationSkeleton->buildFileName(),
                $migrationSkeleton->getClass()->getName(),
                $migrationSkeleton->getFile()->render()
            );
        } catch (RepositoryException $e) {
            $output->writeln('<fg=yellow>Can not create migration</>');
            $output->writeln('<fg=red>' . $e->getMessage() . '</>');
            return;
        }
        $output->writeln('<info>New migration file has been created</info>');
        $output->writeln("<fg=cyan>{$migrationFile}</>");
    }
}
