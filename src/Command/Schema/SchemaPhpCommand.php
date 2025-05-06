<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Schema;

use Cycle\Schema\Renderer\PhpSchemaRenderer;
use Cycle\Schema\Renderer\SchemaToArrayConverter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

#[AsCommand('cycle/schema/php', 'Saves the current schema in a PHP file')]
final class SchemaPhpCommand extends Command
{
    public function __construct(
        private readonly Aliases $aliases,
        private readonly CycleDependencyProxy $promise,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::OPTIONAL, 'file');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $file */
        $file = $input->getArgument('file');

        $converter = new SchemaToArrayConverter();
        $schemaArray = $converter->convert($this->promise->getSchema());

        $content = (new PhpSchemaRenderer())->render($schemaArray);

        if ($file === null) {
            $output->write($content);

            return self::SUCCESS;
        }

        $file = $this->aliases->get($file);
        $output->writeln("Destination: <fg=cyan>$file</>");

        $dir = dirname($file);
        if (!is_dir($dir)) {
            $output->writeln('Destination directory not found.');

            return self::FAILURE;
        }

        if (file_put_contents($file, $content) === false) {
            $output->writeln('<fg=red>Failed to write content to file.</>');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
