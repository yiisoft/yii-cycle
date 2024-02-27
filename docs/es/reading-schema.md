# Reading DB schema

Cycle ORM se basa en un esquema de base de datos, que implementa la interfaz `\Cycle\ORM\SchemaInterface`.

Dado que el esquema se construye a partir de un arreglo con una estructura definida, podemos almacenarlo en caché o en un archivo de texto.

Puede mostrar el esquema utilizado actualmente ejecutando el comando `cycle/schema`.

En el paquete `yiisoft/yii-cycle` el esquema puede ser construido desde múltiples fuentes representadas por múltiples proveedores que implementan
`Cycle\Schema\Provider\SchemaProviderInterface`.

Para utilizar varios proveedores de esquemas, se utiliza el proveedor `Cycle\Schema\Provider\Support\SchemaProviderPipeline`
de agrupación. Puede configurar este proveedor en la sección `schema-providers` en el archivo `config/params.php`.
Los proveedores de esquemas deben estar organizados de la siguiente manera, los proveedores de caché deben estar al principio de la lista y los proveedores de esquemas de origen al final.


## Esquema basado en la anotación de entidades

Por defecto, el esquema se construye en base a las anotaciones que se encuentran en las entidades de su proyecto.

Cuando se construye un esquema, los generadores se ejecutan secuencialmente. La secuencia se determina en una instancia de
`SchemaConveyorInterface`. Puede insertar sus propios generadores dentro del transpotador, para ello debe especificarlos en
`entity-paths` dentro del archivo `config/params.php`.

Para obtener un esquema del transportador se usa la clase `FromConveyorSchemaProvider`.

El proceso de construcción de esquemas a partir de anotaciones es relativamente pesado en términos de rendimiento. Por lo tanto, en caso de
usar anotaciones es una buena idea usar el caché de esquemas.

## Esquemas desde caché

La lectura y escritura de un esquema desde y hacia la caché ocurre en `Cycle\Schema\Provider\SimpleCacheSchemaProvider`.

Debe indicarse al principio de la lista de proveedores para que el proceso de obtención de un esquema sea significativamente más rápido.

## Esquemas basados en archivos

Si quiere evitar las anotaciones, puede describir un esquema en un archivo PHP.
Utilice `Cycle\Schema\Provider\FromFilesSchemaProvider` para cargar un esquema:

```php
# config/common.php
use Cycle\Schema\Provider\FromFilesSchemaProvider;

return [
    // ...
    'yiisoft/yii-cycle' => [
        // ...
        'schema-providers' => [
            FromFilesSchemaProvider::class => FromFilesSchemaProvider::config(fiels: ['@runtime/schema.php']),
        ],
    ]
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

Tenga en cuenta que:

1. `FromFilesSchemaProvider` carga un esquema desde un archivo `PHP` a través de `include`. Esto requiere precauciones de seguridad.
   Asegúrese de almacenar el archivo del esquema en una ruta segura y restringida a los usuarios.
2. Puede especificar varios archivos de esquema, los cuales se fusionarán en un solo esquema.
Se lanzará una excepción en caso de colisión de roles.

3. Gracias a la caché interna, la carga del esquema desde un archivo `PHP` es tan rápida que puede omitirse por completo la caché externa.
En el caso de que se carguen varios archivos, puede tardar más tiempo en fusionase.
4. No se pueden generar migraciones basadas en el esquema de archivos `PHP`. [Para más información mire el issue #25](https://github.com/yiisoft/yii-cycle/issues/25)
5. El proveedor sólo lee el esquema. No puede actualizar el archivo después de aplicar la migración, como hace `SimpleCacheSchemaProvider`.

## Construir el esquema de la base de datos a partir de diferentes proveedores

Para fusionar partes del esquema obtenidas de diferentes proveedores, utilice `Cycle\Schema\Provider\MergeSchemaProvider`.

```php
# runtime/schema.php
return [
    // ...
    'yiisoft/yii-cycle' => [
        // ...
        'schema-providers' => [
            \Cycle\Schema\Provider\MergeSchemaProvider::class => [
                // Puede especificar la clase de proveedor como clave y la configuración como valor.
                // Para generar un arreglo de configuración, puede utilizar el método estático `config()` de
                // la clase del proveedor. En este caso, estará disponible el autocompletado.
                \Cycle\Schema\Provider\FromFilesSchemaProvider::class => ['files' => ['@src/schema.php']],
                // Si necesita utilizar varios proveedores de esquemas con el mismo nombre,
                // el proveedor y su configuración se pueden pasar como un arreglo de dos elementos.
                [\Cycle\Schema\Provider\SimpleCacheSchemaProvider::class, ['key' => 'cycle-schema']],
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

Para exportar el esquema como archivo `PHP` se puede utilizar el comando `cycle/schema/php`.
Especifique el nombre del archivo como argumento y el esquema se escribirá en él:

```bash
cycle/schema/php @runtime/schema.php
```

`@runtime` se sustituye automáticamente. El esquema se exportará al archivo `schema.php`.

Asegúrese de que el esquema exportado sea correcto y luego se puede utilizar mediante `FromFilesSchemaProvider`.

Se pueden combinar ambas formas para describir un esquema. Durante el desarrollo de un proyecto es muy útil utilizar anotaciones. Puede generar
migraciones basadas en ellas. Para el uso en producción, el esquema puede ser trasladado a un archivo.

### Provider `PhpFileSchemaProvider`

A diferencia de `FromFilesSchemaProvider`, el archivo `Cycle\Schema\Provider\PhpFileSchemaProvider` trabaja con un solo
archivo de esquema `PhpFileSchemaProvider` no sólo puede leer el esquema, sino también guardarlo.

En el modo de lectura y escritura un archivo de esquema que utilice el proveedor `PhpFileSchemaProvider`, funcionara de forma similar a la caché, con
la única diferencia que el resultado guardado (archivo de esquema), puede ser guardado en codebase.
