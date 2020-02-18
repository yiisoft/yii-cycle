# Reading DB schema

Cycle ORM relies on DB schema that is represented as an instance of `\Cycle\ORM\SchemaInterface`.

Since schema is built from an array of a certain structure, we can store it either in a cache or in a text file.

You can display currently used schema by executing `cycle/schema` command.

## Entity annotations

By default schema is built based on annotations that are in your project entities.

When building a schema generators are executed sequentially. The sequence is determined in an instance of
`SchemaConveyorInterface`. You can insert your own generators in this conveyor by defining them in
`cycle.common.generators` option of `config/params.php` file.

In order to get a schema from conveyor `SchemaFromConveyorFactory` is used.

The process of building schema from annotations is relatively heavy in terms of performance. Therefore, in case of
using annotations it is a good idea to use schema cache.

## Schema cache

In case of using annotations caching is used by default in `SchemaFromConveyorFactory`. You can configure caching in
`config/params.php`. Two options are available: `cacheEnabled` and `cacheKey`.

```php
# config/params.php
return [
    # ...
    // Common Cycle options
    'cycle.common' => [
        // Settings for annotation-based schema
        // Use cache
        'cacheEnabled' => true,
        // Name of the cache key
        'cacheKey' => 'Cycle-ORM-Schema',
        // A list of paths to directories with entities
        'entityPaths' => [
            # ...
        ],
        # ...
    ],
    # ...
];
```

## File-based schema

If you want to avoid annotations, you can describe a schema in a PHP file.
Use `SchemaFromFileFactory` to load a schema:

```php
# config/common.php
use Yiisoft\Yii\Cycle\Factory\SchemaFromFileFactory;
return [
    # ...
    \Cycle\ORM\SchemaInterface::class => new SchemaFromFileFactory('@runtime/schema.php'),
    # ...
];
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

In case ready schema is loaded from a file there is no need to build it so generators conveyor won't be executed
improving performance significantly. The downside of this approach is that you can not generate migrations based
on current schema by using `migrate/generate`.

## Switching from annotations to file

In order to export schema as PHP file `cycle/schema/php` command could be used:

```bash
cycle/schema/php @runtime/schema.php
```

`@runtime` alias is replaced automatically. Schema will be exported into `schema.php` file.

Make sure that schema exported is correct and then switch to using it via `SchemaFromFileFactory`:

```php
# config/common.php
use Yiisoft\Yii\Cycle\Factory\SchemaFromFileFactory;
return [
    # ...
    \Cycle\ORM\SchemaInterface::class => new SchemaFromFileFactory('@runtime/schema.php'),
    # ...
];
```

After config cache is updated (you can force it with `composer dump-autoload`), schema will be loaded from a PHP-file.
Annotations won't be used anymore.

You can combine both ways to describe a schema. During project development it's handy to use annotations. You can generate
migrations based on them. For production use schema could be moved into a file.
