<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Schema;

use Cycle\Schema\Renderer\PhpSchemaRenderer;
use Cycle\Schema\Renderer\SchemaToArrayConverter;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

final class SchemaPhpCommand extends Command
{
    protected static $defaultName = 'cycle/schema/php';
    protected static $defaultDescription = 'Saves the current schema in a PHP file';

    public function __construct(private readonly Aliases $aliases, private readonly CycleDependencyProxy $promise)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::OPTIONAL, 'file');
    }

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

        try {
            $result = file_put_contents($file, $content);
        } catch (Exception) {
            $result = false;
        }

        if ($result === false) {
            $output->writeln('<fg=red>Failed to write content to file.</>');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
