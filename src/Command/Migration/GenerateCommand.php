<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Migration;

use Cycle\Migrations\GenerateMigrations;
use Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Spiral\Migrations\State;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Schema\Generator\PrintChanges;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;

final class GenerateCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'migrate/generate';

    public function configure(): void
    {
        $this->setDescription('Generates a migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrator = $this->promise->getMigrator();
        // check existing unapplied migrations
        $listAfter = $migrator->getMigrations();
        foreach ($listAfter as $migration) {
            if ($migration
                    ->getState()
                    ->getStatus() !== State::STATUS_EXECUTED) {
                $output->writeln('<fg=red>Outstanding migrations found, run `migrate/up` first.</>');
                return ExitCode::OK;
            }
        }
        $conveyor = $this->promise->getSchemaConveyor();

        // migrations generator
        $conveyor->addGenerator(
            SchemaConveyorInterface::STAGE_USERLAND,
            new GenerateMigrations($migrator->getRepository(), $this->promise->getMigrationConfig())
        );
        // show DB changes
        $conveyor->addGenerator(SchemaConveyorInterface::STAGE_USERLAND, new PrintChanges($output));
        // compile schema and convert diffs to new migrations
        (new Compiler())->compile(new Registry($this->promise->getDatabaseProvider()), $conveyor->getGenerators());

        // compare migrations list before and after
        $listBefore = $migrator->getMigrations();
        $added = count($listBefore) - count($listAfter);
        $output->writeln("<info>Added {$added} file(s)</info>");

        // print added migrations
        if ($added > 0) {
            foreach ($listBefore as $migration) {
                if ($migration
                        ->getState()
                        ->getStatus() !== State::STATUS_EXECUTED) {
                    $output->writeln($migration
                        ->getState()
                        ->getName());
                }
            }
        } else {
            $output->writeln(
                '<info>If you want to create new empty migration, use <fg=yellow>migrate/create</></info>'
            );

            $qaHelper = $this->getHelper('question');

            if ($input->isInteractive() && $input instanceof StreamableInputInterface) {
                $question = new ConfirmationQuestion('Would you like to create empty migration right now? (Y/n)', true);
                $answer = $qaHelper->ask($input, $output, $question);
                if (!$answer) {
                    return ExitCode::OK;
                }
                // get the name for a new migration
                $question = new Question('Please enter an unique name for the new migration: ');
                $name = $qaHelper->ask($input, $output, $question);
                if (empty($name)) {
                    $output->writeln('<fg=red>You entered an empty name. Exit</>');
                    return ExitCode::OK;
                }
                // create an empty migration
                $this->createEmptyMigration($output, $name);
            }
        }
        return ExitCode::OK;
    }
}
