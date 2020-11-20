<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Converter\SchemaToPHP;

use Cycle\ORM\Relation;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Relation\RelationSchema;

final class SchemaRenderer
{
    private const RELATION = [
        Relation::HAS_ONE => 'Relation::HAS_ONE',
        Relation::HAS_MANY => 'Relation::HAS_MANY',
        Relation::BELONGS_TO => 'Relation::BELONGS_TO',
        Relation::REFERS_TO => 'Relation::REFERS_TO',
        Relation::MANY_TO_MANY => 'Relation::MANY_TO_MANY',
        Relation::BELONGS_TO_MORPHED => 'Relation::BELONGS_TO_MORPHED',
        Relation::MORPHED_HAS_ONE => 'Relation::MORPHED_HAS_ONE',
        Relation::MORPHED_HAS_MANY => 'Relation::MORPHED_HAS_MANY',
    ];
    private const RELATION_OPTION = [
        Relation::MORPH_KEY => 'Relation::MORPH_KEY',
        Relation::CASCADE => 'Relation::CASCADE',
        Relation::NULLABLE => 'Relation::NULLABLE',
        Relation::OUTER_KEY => 'Relation::OUTER_KEY',
        Relation::INNER_KEY => 'Relation::INNER_KEY',
        Relation::WHERE => 'Relation::WHERE',
        Relation::THROUGH_INNER_KEY => 'Relation::THROUGH_INNER_KEY',
        Relation::THROUGH_OUTER_KEY => 'Relation::THROUGH_OUTER_KEY',
        Relation::THROUGH_ENTITY => 'Relation::THROUGH_ENTITY',
        Relation::THROUGH_WHERE => 'Relation::THROUGH_WHERE',
        RelationSchema::INDEX_CREATE => 'RelationSchema::INDEX_CREATE',
        RelationSchema::FK_CREATE => 'RelationSchema::FK_CREATE',
        RelationSchema::FK_ACTION => 'RelationSchema::FK_ACTION',
        RelationSchema::INVERSE => 'RelationSchema::INVERSE',
        RelationSchema::MORPH_KEY_LENGTH => 'RelationSchema::MORPH_KEY_LENGTH',
    ];
    private const PREFETCH_MODE = [
        Relation::LOAD_PROMISE => 'Relation::LOAD_PROMISE',
        Relation::LOAD_EAGER => 'Relation::LOAD_EAGER',
    ];
    private const GENERAL_OPTION = [
        Relation::TYPE => 'Relation::TYPE',
        Relation::TARGET => 'Relation::TARGET',
        Relation::SCHEMA => 'Relation::SCHEMA',
        Relation::LOAD => 'Relation::LOAD',
    ];

    private SchemaInterface $schema;

    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    public function render(): string
    {
        $arrayToString = new ArrayItemExporter(null);
        foreach ($this->schema->getRoles() as $role) {
            $arrayToString->value[] = $this->renderRole($role);
        }
        return (string)$arrayToString;
    }

    private function renderRole(string $role): ?ArrayItemExporter
    {
        $aliasOf = $this->schema->resolveAlias($role);
        if ($aliasOf !== null && $aliasOf !== $role) {
            // This role is an alias
            return null;
        }
        if ($this->schema->defines($role) === false) {
            // Role has no definition within the schema
            return null;
        }
        return new ArrayItemExporter($role, [
            $this->renderDatabase($role),
            $this->renderTable($role),
            $this->renderEntity($role),
            $this->renderMapper($role),
            $this->renderRepository($role),
            $this->renderScope($role),
            $this->renderPK($role),
            $this->renderFields($role),
            $this->renderTypecast($role),
            $this->renderRelations($role),
        ], true);
    }

    private function renderDatabase(string $role): ArrayItemExporter
    {
        return new ArrayItemExporter('Schema::DATABASE', $this->schema->define($role, Schema::DATABASE));
    }

    private function renderTable(string $role): ArrayItemExporter
    {
        return new ArrayItemExporter('Schema::TABLE', $this->schema->define($role, Schema::TABLE));
    }

    private function renderEntity(string $role): ArrayItemExporter
    {
        return new ArrayItemExporter('Schema::ENTITY', $this->schema->define($role, Schema::ENTITY));
    }

    private function renderMapper(string $role): ArrayItemExporter
    {
        return new ArrayItemExporter('Schema::MAPPER', $this->schema->define($role, Schema::MAPPER));
    }

    private function renderRepository(string $role): ArrayItemExporter
    {
        return new ArrayItemExporter('Schema::REPOSITORY', $this->schema->define($role, Schema::REPOSITORY));
    }

    private function renderScope(string $role): ArrayItemExporter
    {
        return new ArrayItemExporter('Schema::CONSTRAIN', $this->schema->define($role, Schema::CONSTRAIN));
    }

    private function renderPK(string $role): ArrayItemExporter
    {
        return new ArrayItemExporter('Schema::PRIMARY_KEY', $this->schema->define($role, Schema::PRIMARY_KEY));
    }

    private function renderFields(string $role): ArrayItemExporter
    {
        return new ArrayItemExporter('Schema::COLUMNS', $this->schema->define($role, Schema::COLUMNS));
    }

    private function renderTypecast(string $role): ArrayItemExporter
    {
        return new ArrayItemExporter('Schema::TYPECAST', $this->schema->define($role, Schema::TYPECAST));
    }

    private function renderRelations(string $role): ArrayItemExporter
    {
        $relations = $this->schema->define($role, Schema::RELATIONS) ?? [];
        $results = [];
        foreach ($relations as $field => $relation) {
            $relationResult = [];
            foreach ($relation as $option => $value) {
                $relationResult[] = $this->renderRelationOption($option, $value);
            }
            $results[] = new ArrayItemExporter($field, $relationResult, true);
        }
        return new ArrayItemExporter('Schema::RELATIONS', $results);
    }

    private function renderRelationOption(int $option, $value): ArrayItemExporter
    {
        $item = new ArrayItemExporter(self::GENERAL_OPTION[$option] ?? (string)$option, $value);

        // replace numeric keys and values with constants
        if ($option === Relation::LOAD && array_key_exists($value, self::PREFETCH_MODE)) {
            $item->value = self::PREFETCH_MODE[$value];
            $item->wrapValue = false;
        } elseif ($option === Relation::TYPE && array_key_exists($value, self::RELATION)) {
            $item->value = self::RELATION[$value];
            $item->wrapValue = false;
        } elseif ($option === Relation::SCHEMA && is_array($value)) {
            $item->value = $this->renderRelationSchemaKeys($value);
        }

        return $item;
    }

    private function renderRelationSchemaKeys(array $value): array
    {
        $result = [];
        foreach ($value as $listKey => $listValue) {
            $result[] = new ArrayItemExporter(
                array_key_exists($listKey, self::RELATION_OPTION) ? self::RELATION_OPTION[$listKey] : (string)$listKey,
                $listValue
            );
        }
        return $result;
    }
}
