# Reading DB schema

Cycle ORM relies on DB schema - object, that implement `\Cycle\ORM\SchemaInterface` interface.

Since a schema is built from an array of a certain structure, we can store it either in a cache or in a text file.

You can display currently used schema by executing `cycle/schema` command.

In `yii-cycle` package schema can be built from multiple sources represented by multiple providers implementing
`SchemaProviderInterface`. 

In order to use multiple schema providers in turn, grouping `SchemaProviderPipeline` provider is used.
You can configure this provider in `schema-providers` section of a `config/params.php` file.
Arrage schema providers in such an order, that caching providers are at the top of the list, 
and origin schema providers at the end. 


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
Use `FromFilesSchemaProvider` to load a schema:

```php
# config/common.php
[
    'yiisoft/yii-cycle' => [
        // ...
        'schema-providers' => [
            \Yiisoft\Yii\Cycle\Schema\Provider\FromFilesSchemaProvider::class => [
                'files' => '@runtime/schema.php'
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

1. `FromFilesSchemaProvider` loads a schema from PHP-files via `include`. That requires security precautions.
   Make sure you store schema file in a safe path restricted from users.
2. You can specify multiple schema files, which will be merged into one schema. 
Exception will be thrown in case of collision of roles.

3. Thanks to internal cache, loading schema from a PHP-file is so fast that you can skip an external cache at all.
But in case of loading multiple files, it may take extra time to merge them.
4. You cannot generate migrations based on PHP-file schema. [See issue #25](https://github.com/yiisoft/yii-cycle/issues/25)
5. Provider only reads schema. It cannot update the file after migration is applied, as `SimpleCacheSchemaProvider` does.

## Switching from annotations to file

### Consloe command

In order to export schema as PHP file `cycle/schema/php` command could be used.
Specify a file name as an argument and schema will be written into it:


```bash
cycle/schema/php @runtime/schema.php
```

`@runtime` alias is replaced automatically. Schema will be exported into `schema.php` file.

Make sure schema exported is correct and then switch to using it via `FromFilesSchemaProvider`.

You can combine both ways to describe a schema. During project development it's handy to use annotations. You can generate
migrations based on them. For production use schema could be moved into a file.

### `PhpFileSchemaProvider` Provider

Unlike `FromFilesSchemaProvider`, the `PhpFileSchemaProvider` works with only one file. but, `PhpFileSchemaProvider`
can not only read schema, but also save it.

In the mode of reading and writing a schema file, the `PhpFileSchemaProvider` provider works similarly to the cache, with
the only difference is that saved result (schema file), can be saved in codebase.
