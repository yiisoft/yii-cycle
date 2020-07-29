<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Common;

use Cycle\ORM\Relation;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

final class SchemaCommand extends Command
{
    protected static $defaultName = 'cycle/schema';

    private CycleDependencyProxy $promise;
    private const STR_RELATION = [
        Relation::HAS_ONE => 'has one',
        Relation::HAS_MANY => 'has many',
        Relation::BELONGS_TO => 'belongs to',
        Relation::REFERS_TO => 'refers to',
        Relation::MANY_TO_MANY => 'many to many',
        Relation::BELONGS_TO_MORPHED => 'belongs to morphed',
        Relation::MORPHED_HAS_ONE => 'morphed has one',
        Relation::MORPHED_HAS_MANY => 'morphed has many',
    ];
    private const STR_PREFETCH_MODE = [
        Relation::LOAD_PROMISE => 'promise',
        Relation::LOAD_EAGER => 'eager',
    ];

    public function __construct(CycleDependencyProxy $promise)
    {
        $this->promise = $promise;
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Shown current schema');
        $this->addArgument('role', InputArgument::OPTIONAL, 'Roles to display (separated by ",").');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $roleArgument = $input->getArgument('role');
        $result = true;
        $schema = $this->promise->getSchema();
        $roles = $roleArgument !== null ? explode(',', $roleArgument) : $schema->getRoles();

        foreach ($roles as $role) {
            $result = $this->displaySchema($schema, $role, $output) && $result;
        }

        return $result ? ExitCode::OK : ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Write a role schema in the output
     *
     * @param SchemaInterface $schema Data schema
     * @param string $role Role to display
     * @param OutputInterface $output Output console
     * @return bool
     */
    private function displaySchema(SchemaInterface $schema, string $role, OutputInterface $output): bool
    {
        if (!$schema->defines($role)) {
            $output->writeln("<fg=red>Role</> <fg=magenta>[{$role}]</> <fg=red>not defined!</>");
            return false;
        }

        $output->write("<fg=magenta>[{$role}</>");
        $alias = $schema->resolveAlias($role);
        // alias
        if ($alias !== null && $alias !== $role) {
            $output->write("=><fg=magenta>{$alias}</>");
        }
        $output->write("<fg=magenta>]</>");

        // database and table
        $database = $schema->define($role, Schema::DATABASE);
        $table = $schema->define($role, Schema::TABLE);
        if ($database !== null) {
            $output->write(" :: <fg=green>{$database}</>.<fg=green>{$table}</>");
        }
        $output->writeln('');

        // Entity
        $entity = $schema->define($role, Schema::ENTITY);
        $output->write('   Entity     : ');
        $output->writeln($entity === null ? 'no entity' : "<fg=blue>{$entity}</>");
        // Mapper
        $mapper = $schema->define($role, Schema::MAPPER);
        $output->write('   Mapper     : ');
        $output->writeln($mapper === null ? 'no mapper' : "<fg=blue>{$mapper}</>");
        // Constrain
        $constrain = $schema->define($role, Schema::CONSTRAIN);
        $output->write('   Constrain  : ');
        $output->writeln($constrain === null ? 'no constrain' : "<fg=blue>{$constrain}</>");
        // Repository
        $repository = $schema->define($role, Schema::REPOSITORY);
        $output->write('   Repository : ');
        $output->writeln($repository === null ? 'no repository' : "<fg=blue>{$repository}</>");
        // PK
        $pk = $schema->define($role, Schema::PRIMARY_KEY);
        $output->write('   Primary key: ');
        $output->writeln($pk === null ? 'no primary key' : "<fg=green>{$pk}</>");
        // Fields
        $columns = $schema->define($role, Schema::COLUMNS);
        $output->writeln("   Fields     :");
        $output->writeln("     (<fg=cyan>property</> -> <fg=green>db.field</> -> <fg=blue>typecast</>)");
        $types = $schema->define($role, Schema::TYPECAST);
        foreach ($columns as $property => $field) {
            $typecast = $types[$property] ?? $types[$field] ?? null;
            $output->write("     <fg=cyan>{$property}</> -> <fg=green>{$field}</>");
            if ($typecast !== null) {
                $output->write(" -> <fg=blue>{$typecast}</>");
            }
            $output->writeln('');
        }

        // Relations
        $relations = $schema->define($role, Schema::RELATIONS);
        if (count($relations) > 0) {
            $output->writeln('   Relations  :');
            foreach ($relations as $field => $relation) {
                $type = self::STR_RELATION[$relation[Relation::TYPE] ?? ''] ?? '?';
                $target = $relation[Relation::TARGET] ?? '?';
                $loading = self::STR_PREFETCH_MODE[$relation[Relation::LOAD] ?? ''] ?? '?';
                $relSchema = $relation[Relation::SCHEMA];
                $innerKey = $relSchema[Relation::INNER_KEY] ?? '?';
                $outerKey = $relSchema[Relation::OUTER_KEY] ?? '?';
                $where = $relSchema[Relation::WHERE] ?? [];
                $cascade = $relSchema[Relation::CASCADE] ?? null;
                $cascadeStr = $cascade ? 'cascaded' : 'not cascaded';
                $nullable = $relSchema[Relation::NULLABLE] ?? null;
                $nullableStr = $nullable ? 'nullable' : ($nullable === false ? 'not null' : 'n/a');
                $morphKey = $relSchema[Relation::MORPH_KEY] ?? null;
                // Many-To-Many relation(s) options
                $mmInnerKey = $relSchema[Relation::THROUGH_INNER_KEY] ?? '?';
                $mmOuterKey = $relSchema[Relation::THROUGH_OUTER_KEY] ?? '?';
                $mmEntity = $relSchema[Relation::THROUGH_ENTITY] ?? null;
                $mmWhere = $relSchema[Relation::THROUGH_WHERE] ?? [];
                // print
                $output->write(
                    "     <fg=magenta>{$role}</>-><fg=cyan>{$field}</> "
                    . "{$type} <fg=magenta>{$target}</> {$loading} load"
                );
                if ($morphKey !== null) {
                    $output->writeln("       Morphed key: <fg=green>{$morphKey}</>");
                }
                $output->writeln(" <fg=yellow>{$cascadeStr}</>");
                $output->write("       {$nullableStr} <fg=green>{$table}</>.<fg=green>{$innerKey}</> <=");
                if ($mmEntity !== null) {
                    $output->write(" <fg=magenta>{$mmEntity}</>.<fg=green>{$mmInnerKey}</>");
                    $output->write("|");
                    $output->write("<fg=magenta>{$mmEntity}</>.<fg=green>{$mmOuterKey}</> ");
                }
                $output->writeln("=> <fg=magenta>{$target}</>.<fg=green>{$outerKey}</> ");
                if (count($where)) {
                    $output->write("       Where:");
                    $output->writeln(str_replace(["\r\n", "\n"], "\n       ", "\n" . print_r($where, 1)));
                }
                if (count($mmWhere)) {
                    $output->write("       Through where:");
                    $output->writeln(str_replace(["\r\n", "\n"], "\n       ", "\n" . print_r($mmWhere, 1)));
                }
            }
        } else {
            $output->writeln('   No relations');
        }
        return true;
    }
}
