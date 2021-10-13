# Reading DB schema

Cycle ORM se basa en el esquema de la base de datos - objeto, que implementa la interfaz `\Cycle\ORM\SchemaInterface`.

Dado que un esquema se construye a partir de un array de una determinada estructura, podemos almacenarlo en caché o en un archivo de texto.

Puede mostrar el esquema utilizado actualmente ejecutando el comando `cycle/schema`.

En el paquete `yii-cycle` el esquema puede ser construido desde múltiples fuentes representadas por múltiples proveedores que implementan
`SchemaProviderInterface`.

Para utilizar varios proveedores de esquemas a su vez, se utiliza el proveedor `SchemaProviderPipeline` de agrupación.
Puede configurar este proveedor en la sección `schema-providers` de un archivo `config/params.php`.
Organice los proveedores de esquemas en un orden tal, que los proveedores de caché estén al principio de la lista y los proveedores de esquemas de origen al final.


## Esquema basado en la anotación de entidades

Por defecto, el esquema se construye en base a las anotaciones que se encuentran en las entidades de su proyecto.

Cuando se construye un esquema, los generadores se ejecutan secuencialmente. La secuencia se determina en una instancia de
`SchemaConveyorInterface`. Puede insertar sus propios generadores en este transportador definiéndolos en
`entity-paths` una opción del archivo `config/params.php`.

Para obtener un esquema del transportador se usa la clase `FromConveyorSchemaProvider`.

El proceso de construcción de esquemas a partir de anotaciones es relativamente pesado en términos de rendimiento. Por lo tanto, en caso de
usar anotaciones es una buena idea usar el caché de esquemas.

## Esquemas desde caché

La lectura y escritura de un esquema desde y hacia la caché ocurre en `SimpleCacheSchemaProvider`.

Colócalo al principio de la lista de proveedores para que el proceso de obtención de un esquema sea significativamente más rápido.

## Esquemas basados en archivos

Si quiere evitar las anotaciones, puede describir un esquema en un archivo PHP.
Utilice `FromFilesSchemaProvider` para cargar un esquema:

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

Tenga en cuenta que:

1. `FromFilesSchemaProvider` carga un esquema desde archivos PHP a través de `include`. Esto requiere precauciones de seguridad.
   Asegúrese de almacenar el archivo del esquema en una ruta segura y restringida a los usuarios.
2. Puede especificar varios archivos de esquema, que se fusionarán en un solo esquema.
Se lanzará una excepción en caso de colisión de roles.

3. Gracias a la caché interna, la carga del esquema desde un archivo PHP es tan rápida que puede omitirse por completo la caché externa.
Pero en el caso de que se carguen varios archivos, puede tardar más tiempo en fusionarlos.
4. No se pueden generar migraciones basadas en el esquema de archivos PHP. [Para más información mire el issue #25](https://github.com/yiisoft/yii-cycle/issues/25)
5. El proveedor sólo lee el esquema. No puede actualizar el archivo después de aplicar la migración, como hace `SimpleCacheSchemaProvider`.

## Construir el esquema de la base de datos a partir de diferentes proveedores

Para fusionar partes del esquema obtenidas de diferentes proveedores, utilice `MergeSchemaProvider`.

```php
# runtime/schema.php
return [
    // ...
    'yiisoft/yii-cycle' => [
        // ...
        'schema-providers' => [
            \Yiisoft\Yii\Cycle\Schema\Provider\Support\MergeSchemaProvider::class => [
                // Puede especificar la clase de proveedor como clave y la configuración como valor.
                \Yiisoft\Yii\Cycle\Schema\Provider\FromFilesSchemaProvider::class => ['files' => ['@src/schema.php']],
                // El proveedor y su configuración pueden pasarse como un array.
                [\Yiisoft\Yii\Cycle\Schema\Provider\SimpleCacheSchemaProvider::class, ['key' => 'cycle-schema']],
                // Al definir la dependencia como una cadena, asegúrese de que el contenedor proporciona
                // el proveedor ya configurado.
                \Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider::class,
            ]
        ],
    ]
];
```

## Pasar de las anotaciones al archivo

### Comando de consola

Para exportar el esquema como archivo PHP se puede utilizar el comando `cycle/schema/php`.
Especifique un nombre de archivo como argumento y el esquema se escribirá en él:

```bash
cycle/schema/php @runtime/schema.php
```

`@runtime` se sustituye automáticamente. El esquema se exportará al archivo `schema.php`.

Asegúrese de que el esquema exportado es correcto y luego pase a utilizarlo mediante `FromFilesSchemaProvider`.

Se pueden combinar ambas formas para describir un esquema. Durante el desarrollo de un proyecto es muy útil utilizar anotaciones. Puede generar
migraciones basadas en ellas. Para el uso en producción, el esquema puede ser trasladado a un archivo.

### Provider `PhpFileSchemaProvider`

A diferencia de `FromFilesSchemaProvider`, el `PhpFileSchemaProvider` trabaja con un solo archivo. pero, `PhpFileSchemaProvider`
no sólo puede leer el esquema, sino también guardarlo.

En el modo de lectura y escritura de un archivo de esquema, el proveedor `PhpFileSchemaProvider` funciona de forma similar a la caché, con
la única diferencia es que el resultado guardado (archivo de esquema), puede ser guardado en codebase.
