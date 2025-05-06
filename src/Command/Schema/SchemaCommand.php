<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Schema;

use Cycle\Schema\Renderer\OutputSchemaRenderer;
use Cycle\Schema\Renderer\SchemaToArrayConverter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

#[AsCommand('cycle/schema', 'Shown current schema')]
final class SchemaCommand extends Command
{
    public function __construct(private readonly CycleDependencyProxy $promise)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument('role', InputArgument::OPTIONAL, 'Roles to display (separated by ",").');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $roleArgument */
        $roleArgument = $input->getArgument('role');
        $schema = $this->promise->getSchema();
        $roles = $roleArgument !== null ? explode(',', $roleArgument) : $schema->getRoles();

        $schemaArray = (new SchemaToArrayConverter())->convert($schema);

        $notFound = [];
        $found = [];
        foreach ($roles as $role) {
            if (!\array_key_exists($role, $schemaArray)) {
                $notFound[] = $role;
                continue;
            }
            $found[$role] = $schemaArray[$role];
        }
        $renderer = new OutputSchemaRenderer(OutputSchemaRenderer::FORMAT_CONSOLE_COLOR);
        $output->write($renderer->render($found));

        if ($notFound !== []) {
            $output->writeln(sprintf('<fg=red>Undefined roles: %s</>', implode(', ', $notFound)));
        }

        return self::SUCCESS;
    }
}
