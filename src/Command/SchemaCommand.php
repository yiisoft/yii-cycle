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
    private array $strRelations = [
        Relation::HAS_ONE      => 'has one',
        Relation::HAS_MANY     => 'has many',
        Relation::BELONGS_TO   => 'belongs to',
        Relation::REFERS_TO    => 'refers to',
        Relation::MANY_TO_MANY => 'many to many',
    ];
    private array $strPreFetchMode = [
        Relation::LOAD_PROMISE => 'promise',
        Relation::LOAD_EAGER   => 'eager',
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
            $output->write("<fg=red>[{$role}</>");
            $alias = $this->schema->resolveAlias($role);
            // alias
            if ($alias !== null && $alias !== $role) {
                $output->write("=><fg=red>{$alias}</>");
            }
            // table
            $output->write("<fg=red>]</>");

            // database
            $database = $this->schema->define($role, Schema::DATABASE);
            if ($database !== null) {
                $table = $this->schema->define($role, Schema::TABLE);
                $output->write(" :: <fg=green>{$database}</>.<fg=green>{$table}</>");
            }
            $output->writeln('');

            // Entity
            $entity = $this->schema->define($role, Schema::ENTITY) ?? null;
            $output->write('   Entity     : ');
            $output->writeln($entity === null ? '<fg=red>no entity</>' : "<fg=cyan>{$entity}</>");
            // Mapper
            $mapper = $this->schema->define($role, Schema::MAPPER) ?? null;
            $output->write('   Mapper     : ');
            $output->writeln($mapper === null ? '<fg=red>no mapper</>' : "<fg=cyan>{$mapper}</>");
            // Constrain
            $constrain = $this->schema->define($role, Schema::CONSTRAIN) ?? null;
            $output->write('   Constrain  : ');
            $output->writeln($constrain === null ? '<fg=red>no constrain</>' : "<fg=cyan>{$constrain}</>");
            // Repository
            $repository = $this->schema->define($role, Schema::REPOSITORY) ?? null;
            $output->write('   Repository : ');
            $output->writeln($repository === null ? '<fg=red>no repository</>' : "<fg=cyan>{$repository}</>");
            // PK
            $output->writeln('   Primary key: <fg=cyan>' . ($this->schema->define($role, Schema::PRIMARY_KEY) ?? 'no PK') . '</>');
            // $output->writeln('   Find by: <fg=cyan>' . implode(',', $this->schema->define($role, Schema::FIND_BY_KEYS) ?? []) . '</>');

            // Fields
            $columns = $this->schema->define($role, Schema::COLUMNS);
            $output->writeln('   Fields: (entity.property -> db.field -> typecast)');
            $types = $this->schema->define($role, Schema::TYPECAST);
            foreach ($columns as $property => $field) {
                $typecast = $types[$property] ?? $types[$field] ?? null;
                $output->write("     <fg=cyan>{$property}</> -> <fg=cyan>{$field}</>");
                if ($typecast !== null) {
                    $output->write(" -> <fg=green>{$typecast}</>");
                }
                $output->writeln('');
            }

            // Relations
            $relations = $this->schema->define($role, Schema::RELATIONS);
            if (count($relations) > 0) {
                $output->writeln('   Relations:');
                foreach ($relations as $field => $relation) {
                    $type = $this->strRelations[$relation[Relation::TYPE] ?? ''] ?? '?';
                    $target = $relation[Relation::TARGET] ?? '?';
                    $loading = $this->strPreFetchMode[$relation[Relation::LOAD] ?? ''] ?? '?';
                    $relSchema = $relation[Relation::SCHEMA];
                    $innerKey = $relSchema[Relation::INNER_KEY] ?? '?';
                    $outerKey = $relSchema[Relation::OUTER_KEY] ?? '?';
                    $where = $relSchema[Relation::WHERE] ?? [];
                    $cascade = $relSchema[Relation::CASCADE] ?? null;
                    $cascadeStr = $cascade ? 'cascaded' : 'not cascaded';
                    $nullable = $relSchema[Relation::NULLABLE] ?? null;
                    $nullableStr = $nullable ? 'nullable' : ($nullable === false ? 'not null' : 'n/a');
                    // Many-To-Many relation(s) options
                    $mmInnerKey = $relSchema[Relation::THROUGH_INNER_KEY] ?? '?';
                    $mmOuterKey = $relSchema[Relation::THROUGH_OUTER_KEY] ?? '?';
                    $mmEntity = $relSchema[Relation::THROUGH_ENTITY] ?? null;
                    $mmWhere = $relSchema[Relation::THROUGH_WHERE] ?? [];
                    // print
                    $output->write("     <fg=cyan>{$field}</> {$type} <fg=cyan>{$target}</> {$loading} load");
                    $output->writeln(" <fg=yellow>{$cascadeStr}</>");
                    $output->write("       {$nullableStr} <fg=cyan>{$role}</>.<fg=cyan>{$innerKey}</> <=");
                    if ($mmEntity !== null) {
                        $output->write(" <fg=yellow>{$mmEntity}</>.<fg=yellow>{$mmInnerKey}</>");
                        $output->write("|");
                        $output->write("<fg=yellow>{$mmEntity}</>.<fg=yellow>{$mmOuterKey}</> ");
                    }
                    $output->writeln("=> <fg=cyan>{$target}</>.<fg=cyan>{$outerKey}</> ");
                    if (count($where)) {
                        $output->writeln("       Where: ");
                        $output->writeln('       ' . str_replace(["\r\n", "\n"], "\n       ", print_r($where, 1)));
                    }
                    if (count($mmWhere)) {
                        $output->writeln("       Through where: ");
                        $output->writeln('       ' . str_replace(["\r\n", "\n"], "\n       ", print_r($mmWhere, 1)));
                    }
                }
            } else {
                $output->writeln('   No relations');
            }

        }

        return ExitCode::OK;
    }
}
