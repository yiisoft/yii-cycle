<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Schema;

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
        /** @var string|null $roleArgument */
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
     *
     * @return bool
     */
    private function displaySchema(SchemaInterface $schema, string $role, OutputInterface $output): bool
    {
        if (!$schema->defines($role)) {
            $output->writeln(sprintf('<fg=red>The role</> %s <fg=red>is not defined!</>', $this->wrapRole($role)));
            return false;
        }

        $output->write('[' . $this->wrapRole($role));
        $alias = $schema->resolveAlias($role);
        // alias
        if ($alias !== null && $alias !== $role) {
            $output->write(' => ' . $this->wrapRole($alias));
        }
        $output->write(']');

        // database and table
        $database = $schema->define($role, Schema::DATABASE);
        $table = $schema->define($role, Schema::TABLE);
        if ($database !== null) {
            $output->write(sprintf(' :: %s.%s', $this->wrapDB($database), $this->wrapDB($table)));
        }
        $output->writeln('');

        // Entity
        $entity = $schema->define($role, Schema::ENTITY);
        $output->write('   Entity     : ');
        $output->writeln($entity === null ? 'no entity' : $this->wrapPhp($entity));
        // Mapper
        $mapper = $schema->define($role, Schema::MAPPER);
        $output->write('   Mapper     : ');
        $output->writeln($mapper === null ? 'no mapper' : $this->wrapPhp($mapper));
        // Constrain
        $constrain = $schema->define($role, Schema::CONSTRAIN);
        $output->write('   Constrain  : ');
        $output->writeln($constrain === null ? 'no constrain' : $this->wrapPhp($constrain));
        // Repository
        $repository = $schema->define($role, Schema::REPOSITORY);
        $output->write('   Repository : ');
        $output->writeln($repository === null ? 'no repository' : $this->wrapPhp($repository));
        // PK
        $pk = $schema->define($role, Schema::PRIMARY_KEY);
        $output->write('   Primary key: ');
        $output->writeln($pk === null ? 'no primary key' : $this->wrapProperty($pk));
        // Fields
        $columns = $schema->define($role, Schema::COLUMNS);
        $output->write('   Fields     :');
        $output->writeln(sprintf(
            ' (%s -> %s -> %s)',
            $this->wrapProperty('property'),
            $this->wrapDB('db.field'),
            $this->wrapPhp('typecast')
        ));
        $types = $schema->define($role, Schema::TYPECAST);
        foreach ($columns as $property => $field) {
            $typecast = $types[$property] ?? $types[$field] ?? null;
            $output->write(sprintf('     %s -> %s', $this->wrapProperty($property), $this->wrapDB($field)));
            if ($typecast !== null) {
                $output->write(sprintf(' -> %s', $this->wrapPhp(implode('::', (array)$typecast))));
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
                $orderBy = $relSchema[Relation::ORDER_BY] ?? [];
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
                    sprintf(
                        '     %s->%s %s %s %s load',
                        $this->wrapRole($role),
                        $this->wrapProperty($field),
                        $type,
                        $this->wrapRole($target),
                        $loading
                    )
                );
                if ($morphKey !== null) {
                    $output->writeln('       Morphed key: ' . $this->wrapProperty($morphKey));
                }
                $output->writeln(" <fg=yellow>{$cascadeStr}</>");
                $output->write(
                    sprintf('       %s %s.%s <=', $nullableStr, $this->wrapRole($role), $this->wrapProperty($innerKey))
                );
                if ($mmEntity !== null) {
                    $output->write(sprintf(' %s.%s', $this->wrapRole($mmEntity), $this->wrapProperty($mmInnerKey)));
                    $output->write('|');
                    $output->write(sprintf('%s.%s ', $this->wrapRole($mmEntity), $this->wrapProperty($mmOuterKey)));
                }
                $output->writeln(sprintf('=> %s.%s ', $this->wrapRole($target), $this->wrapProperty($outerKey)));
                if (count($where)) {
                    $output->write('       Where:');
                    $output->writeln($this->printValue($where, '         '));
                }
                if (count($orderBy)) {
                    $output->writeln('       Order by:');
                    $output->writeln($this->printValue($orderBy, '         '));
                }
                if (count($mmWhere)) {
                    $output->write('       Through where:');
                    $output->writeln($this->printValue($mmWhere, '         '));
                }
            }
        } else {
            $output->writeln('   No relations');
        }
        return true;
    }

    /**
     * @param mixed $value
     */
    private function printValue($value, string $prefix = ''): string
    {
        if (!is_iterable($value)) {
            return (string)$value;
        }
        $result = [];
        foreach ($value as $key => $val) {
            $result[] = "$prefix$key" . (is_iterable($val) ? ":\n" : ' => ') . $this->printValue($val, $prefix . '  ');
        }
        return implode("\n", $result);
    }

    private function wrapRole(string $role): string
    {
        return "<fg=magenta>{$role}</>";
    }

    private function wrapDB(string $entity): string
    {
        return "<fg=green>{$entity}</>";
    }

    private function wrapPhp(string $entity): string
    {
        return "<fg=blue>{$entity}</>";
    }

    private function wrapProperty(string $name): string
    {
        return "<fg=cyan>{$name}</>";
    }
}
