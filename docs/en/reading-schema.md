# Reading DB schema

Cycle ORM relies on DB schema that is represented as an instance of `\Cycle\ORM\SchemaInterface`.

Since a schema is built from an array of a certain structure, we can store it either in a cache or in a text file.

You can display currently used schema by executing `cycle/schema` command.

In `yii-cycle` package schema can be built from multiple sources represented by multiple providers implementing
`SchemaProviderInterface`. According to this interface, additionally to providing (reading) a schema from its storage,
provider can save (overwrite) schema in the storage. You can specify an ordered list of schema providers via 
`schema-providers` option in `config/params.php`.

`SchemaManager` handles the list of schema providers the following way:

- Manager iterates providers querying schema.
- If provider provides a schema, the list iteration ends.
- Schema obtained is passed to providers that were unable to provide a schema.
- If no providers provided schema, an exception manager throws an exception.

## Entity annotation based schema

By default, schema is built based on annotations that are in your project entities.

When building a schema generators are executed sequentially. The sequence is determined in an instance of
`SchemaConveyorInterface`. You can insert your own generators in this conveyor by defining them in
`annotated-entity-paths` option of `config/params.php` file.

In order to get a schema from conveyor `FromConveyorSchemaProvider` is used.

The process of building schema from annotations is relatively heavy in terms of performance. Therefore, in case of
using annotations it is a good idea to use schema cache.

## Schema cache

Reading and writing a schema from and to cache happens in `SimpleCacheSchemaProvider`.

Place it to the beginning of providers list to make the process of obtaining a schema significantly faster.

## File-based schema

If you want to avoid annotations, you can describe a schema in a PHP file.
Use `FromFileSchemaProvider` to load a schema:

```php
# config/common.php
[
    'yiisoft/yii-cycle' => [
        // ...
        'schema-providers' => [
            \Yiisoft\Yii\Cycle\Schema\Provider\FromFileSchemaProvider::class => [
                'file' => '@runtime/schema.php'
            ]
        ],
    ]
```

```php
# runtime/schema.php
use Cycle\ORM\Schema;
return [
   'user' => [
        Schema::MAPPER      => \Cycle\ORM\Mapper\Mapper::class,
        Schema::ENTITY      => \App\Entity\User::class,
        Schema::DATABASE    => 'default',
        Schema::TABLE       => 'users',
        Schema::PRIMARY_KEY => 'id',
        Schema::COLUMNS     => [
           'id'   => 'id',
           'name' => 'name'
        ],
        Schema::TYPECAST    => [
           'id' => 'int'
        ],
        Schema::RELATIONS   => []
    ]
];
```

Note that: 

1. `FromFileSchemaProvider` loads a schema from a PHP-file via `include`. That requires security precautions.
   Make sure you store schema file in a safe path restricted from users.
2. There is no need to build a schema from annotations if you are reading it from a file. Therefore, there is no need
   for `FromConveyorSchemaProvider` in this case.
3. Thanks to internal cache, loading schema from a PHP-file is so fast that you can skip an external cache at all.
4. You cannot generate migrations based on PHP-file schema. [See issue #25](https://github.com/yiisoft/yii-cycle/issues/25)
5. Provider only reads schema. It cannot update the file after migration is applied.

## Switching from annotations to file

In order to export schema as PHP file `cycle/schema/php` command could be used.
Specify a file name as an argument and schema will be written into it:


```bash
cycle/schema/php @runtime/schema.php
```

`@runtime` alias is replaced automatically. Schema will be exported into `schema.php` file.

Make sure schema exported is correct and then switch to using it via `FromFileSchemaProvider`.

You can combine both ways to describe a schema. During project development it's handy to use annotations. You can generate
migrations based on them. For production use schema could be moved into a file.
