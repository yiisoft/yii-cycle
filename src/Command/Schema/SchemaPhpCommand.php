<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Schema;

use Cycle\Schema\Renderer\PhpSchemaRenderer;
use Cycle\Schema\Renderer\SchemaToArrayConverter;
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

        $content = (new PhpSchemaRenderer())
            ->render($schemaArray);

        if ($file !== null) {
            $file = $this->aliases->get($file);
            $output->writeln("Destination: <fg=cyan>{$file}</>");
            // Dir exists
            $dir = dirname($file);
            if (!is_dir($dir)) {
                throw new \RuntimeException("Directory {$dir} not found");
            }
            if (file_put_contents($file, $content) === false) {
                return self::FAILURE;
            }
        } else {
            $output->write($content);
        }
        return self::SUCCESS;
    }
}
