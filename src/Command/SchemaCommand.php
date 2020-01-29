<?php

namespace Yiisoft\Yii\Cycle\Command;

use Cycle\ORM\Relation;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Migrator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;

class SchemaCommand extends BaseMigrationCommand
{
    protected static $defaultName = 'cycle/schema';

    private SchemaInterface $schema;
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

    public function __construct(
        DatabaseManager $dbal,
        MigrationConfig $conf,
        Migrator $migrator,
        SchemaInterface $schema
    ) {
        parent::__construct($dbal, $conf, $migrator);
        $this->schema = $schema;
    }

    public function configure(): void
    {
        $this->setDescription('Shown current schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->schema->getRoles() as $role) {
            $output->write("<fg=magenta>[{$role}</>");
            $alias = $this->schema->resolveAlias($role);
            // alias
            if ($alias !== null && $alias !== $role) {
                $output->write("=><fg=magenta>{$alias}</>");
            }
            // table
            $output->write("<fg=magenta>]</>");

            // database
            $database = $this->schema->define($role, Schema::DATABASE);
            $table = $this->schema->define($role, Schema::TABLE);
            if ($database !== null) {
                $output->write(" :: <fg=green>{$database}</>.<fg=green>{$table}</>");
            }
            $output->writeln('');

            // Entity
            $entity = $this->schema->define($role, Schema::ENTITY);
            $output->write('   Entity     : ');
            $output->writeln($entity === null ? 'no entity' : "<fg=blue>{$entity}</>");
            // Mapper
            $mapper = $this->schema->define($role, Schema::MAPPER);
            $output->write('   Mapper     : ');
            $output->writeln($mapper === null ? 'no mapper' : "<fg=blue>{$mapper}</>");
            // Constrain
            $constrain = $this->schema->define($role, Schema::CONSTRAIN);
            $output->write('   Constrain  : ');
            $output->writeln($constrain === null ? 'no constrain' : "<fg=blue>{$constrain}</>");
            // Repository
            $repository = $this->schema->define($role, Schema::REPOSITORY);
            $output->write('   Repository : ');
            $output->writeln($repository === null ? 'no repository' : "<fg=blue>{$repository}</>");
            // PK
            $pk = $this->schema->define($role, Schema::PRIMARY_KEY);
            $output->write('   Primary key: ');
            $output->writeln($pk === null ? 'no primary key' : "<fg=green>{$pk}</>");
            // Fields
            $columns = $this->schema->define($role, Schema::COLUMNS);
            $output->writeln("   Fields     :");
            $output->writeln("     (<fg=cyan>property</> -> <fg=green>db.field</> -> <fg=blue>typecast</>)");
            $types = $this->schema->define($role, Schema::TYPECAST);
            foreach ($columns as $property => $field) {
                $typecast = $types[$property] ?? $types[$field] ?? null;
                $output->write("     <fg=cyan>{$property}</> -> <fg=green>{$field}</>");
                if ($typecast !== null) {
                    $output->write(" -> <fg=blue>{$typecast}</>");
                }
                $output->writeln('');
            }

            // Relations
            $relations = $this->schema->define($role, Schema::RELATIONS);
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
        }
        return ExitCode::OK;
    }
}
