<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Migration;

use Cycle\Migrations\MigrationInterface;
use Cycle\Migrations\State;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;
use Yiisoft\Yii\Cycle\Event\AfterMigrate;
use Yiisoft\Yii\Cycle\Event\BeforeMigrate;

final class UpCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/up';
    protected static $defaultDescription = 'Executes all new migrations';

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(CycleDependencyProxy $promise, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($promise);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrations = $this->findMigrations($output);
        // check any not executed migration
        $exist = false;
        foreach ($migrations as $migration) {
            if ($migration->getState()->getStatus() === State::STATUS_PENDING) {
                $exist = true;
                break;
            }
        }
        if (!$exist) {
            $output->writeln('<fg=red>No migration found for execute</>');
            return self::SUCCESS;
        }

        $migrator = $this->promise->getMigrator();

        // Confirm
        if (!$migrator->getConfig()->isSafe()) {
            $newMigrations = [];
            foreach ($migrations as $migration) {
                if ($migration->getState()->getStatus() === State::STATUS_PENDING) {
                    $newMigrations[] = $migration;
                }
            }
            $countNewMigrations = count($newMigrations);
            $output->writeln(
                '<fg=yellow>' .
                ($countNewMigrations === 1 ? 'Migration' : $countNewMigrations . ' migrations') .
                ' ' .
                'to be applied:</>'
            );
            foreach ($newMigrations as $migration) {
                $output->writeln('— <fg=cyan>' . $migration->getState()->getName() . '</>');
            }
            if ($input->isInteractive()) {
                $question = new ConfirmationQuestion(
                    'Apply the above ' .
                    ($countNewMigrations === 1 ? 'migration' : 'migrations') .
                    '? (yes|no) ',
                    false
                );
                /** @var QuestionHelper $qaHelper*/
                $qaHelper = $this->getHelper('question');
                if (!$qaHelper->ask($input, $output, $question)) {
                    return self::SUCCESS;
                }
            }
        }

        $limit = PHP_INT_MAX;
        $this->eventDispatcher->dispatch(new BeforeMigrate());
        try {
            do {
                $migration = $migrator->run();
                if (!$migration instanceof MigrationInterface) {
                    break;
                }

                $state = $migration->getState();
                $status = $state->getStatus();
                $output->writeln('<fg=cyan>' . $state->getName() . '</>: '
                    . (self::MIGRATION_STATUS[$status] ?? $status));
            } while (--$limit > 0);
        } finally {
            $this->eventDispatcher->dispatch(new AfterMigrate());
        }
        return self::SUCCESS;
    }
}
