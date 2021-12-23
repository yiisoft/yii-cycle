# EntityReader

`EntityReader` es una herramienta útil para transferir de forma segura las solicitudes de selección del repositorio en tiempo de ejecución del usuario.

Una solicitud de selección se refiere a una instancia de una de las clases: ``CycleORM\Select`` o ``Spiral\Database\Query\SelectQuery``.

Lo que hay que saber sobre `EntityReader`:

* La clase `EntityReader` implementa la interfaz `IteratorAggregate`. Esto permite utilizar el objeto `EntityReader` en un bucle `foreach`.
* Con `EntityReader` se puede ajustar la consulta de selección:
  - Establezca `Limit` y `Offset` manualmente con `OffsetPaginator`.
  - La ordenación por `EntityReader` no sustituye la ordenación en la consulta original, sólo la complementa. Sin embargo, cada llamada posterior al método `withSort()` sustituirá la configuración de clasificación del objeto `EntityReader`.
  - Las condiciones del filtro `EntityReader` tampoco sustituyen al de filtrado en la consulta original, solo la complementan. Así, que al filtrar el objeto `EntityReader`, sólo puede ajustar la selección pero no ampliarla.
* `EntityReader` no extrae los datos de la base de datos de una sola vez. Sólo accede a la base de datos cuando se consultan esos datos.
* Si utiliza los métodos `read()` y `readOne()` para leer los datos, entonces `EntityReader` lo almacenará en una caché. El resultado de una llamada a `count()` también se almacena en caché.
* El método `count()` devuelve el número de todos los elementos de la muestra sin tener en cuenta las restricciones `Limit` y `Offset`.
* Si no quieres que los datos se almacenen en caché, utiliza el método `getIterator()`. Sin embargo, si la caché ya está llena, `getIterator()` devolverá el contenido de la caché.

## Ejemplos

Vamos a implementar un repositorio para trabajar con la tabla de artículos. Queremos un método para obtener artículos públicos `findPublic()`
pero no devolverá la colección de artículos ni la consulta de selección. En su lugar, devolverá una nueva instancia de `EntityReader`:

```php
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

class ArticleRepository extends \Cycle\ORM\Select\Repository
{
    public function findPublic(): DataReaderInterface
    {
        return new EntityReader($this->select()->where(['public' => true]));
    }
}
```

Ahora podemos utilizar `EntityReader` para la paginación como el siguiente ejemplo:

```php
/**
 * @var ArticleRepository $repository
 * @var \Yiisoft\Yii\Cycle\Data\Reader\EntityReader $articles
 */
$articles = $repository->findPublic();

// El límite y el desplazamiento pueden especificarse explícitamente.
// Tercera página:
$pageReader = $articles->withLimit(10)->withOffset(20);

// En su lugar se podría utilizar Paginator.
$paginator = new \Yiisoft\Data\Paginator\OffsetPaginator($articles);
$paginator->withPageSize(10)->withCurrentPage(3);


// Obtención de artículos desde EntityReader con caché:
foreach ($pageReader->read() as $article) {
    // ...
}
// Lo mismo sin caché:
foreach ($pageReader as $article) {
    // ...
}

// Obtención de artículos del paginador:
foreach ($paginator->read() as $article) {
    // ...
}
```

Ahora buscaremos los 20 últimos artículos publicados y luego los 20 primeros.

```php
/**
 * @var \Yiisoft\Yii\Cycle\Data\Reader\EntityReader $articles
 */

// El orden de especificación de los parámetros no es importante, así que empecemos por el límite
$lastPublicReader = $articles->withLimit(20);

// La ordenación se especifica con el objeto Sort:
$sort = \Yiisoft\Data\Reader\Sort::any()->withOrder(['published_at' => 'desc']);
// Tenga en cuenta que EntityReader no comprobará la corrección del campo Sort.
// La especificación de campos inexistentes daría lugar a un error en el código de Cycle.

// No olvide la inmutabilidad al aplicar las reglas de clasificación
$lastPublicReader = $lastPublicReader->withSort($sort);

printf(
    "Last %d published articles of %d total:",
    count($lastPublicReader->read()),
    $lastPublicReader->count()
);
foreach ($lastPublicReader->read() as $article) {
    // ...
}

// Ahora vamos a obtener 20 primeros artículos publicados
$sort = $lastPublicReader->getSort()->withOrder(['published_at' => 'asc']);

// Debido a la inmutabilidad, el objeto Sort no se modificará y
// la ordenación actual de $lastPublicReader seguirá siendo la misma.
// Para aplicar las nuevas reglas de ordenación llame a withSort() una vez más:
$lastPublicReader = $lastPublicReader->withSort($sort);

printf(
    "First %d published articles of %d total:",
    count($lastPublicReader->read()),
    $lastPublicReader->count()
);
foreach ($lastPublicReader->read() as $article) {
    // ...
}
```

La ordenación a través de `EntityReader` no sustituye la ordenación en la consulta inicial, sino que le añade algo más.
Si necesitas establecer la ordenación por defecto en un método del repositorio, pero quieres poder cambiarla en un controlador, puedes puede hacerlo de la siguiente manera:

```php
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

class ArticleRepository extends \Cycle\ORM\Select\Repository
{
    /**
     * @return EntityReader
     */
    public function findPublic(): DataReaderInterface
    {
        $sort = Sort::any()->withOrder(['published_at' => 'desc']);
        return (new EntityReader($this->select()->where(['public' => true])))->withSort($sort);
    }
}

// class SiteController ... {

function index(ArticleRepository $repository)
{
    $articlesReader = $repository
        // Obteniendo EntityReader
        ->findPublic()
        // Aplicando nueva ordenación.
        ->withSort(Sort::any()->withOrder(['published_at' => 'asc']));
}
```
Puede ajustar las condiciones de consulta con filtros. Estas condiciones de filtrado se añaden a las condiciones de consulta de selección originales, pero no las sustituyen.

```php
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

class ArticleRepository extends \Cycle\ORM\Select\Repository
{
    public function findUserArticles(int $userId): DataReaderInterface
    {
        return (new EntityReader($this->select()->where('user_id', $userId)))
            // Añadiendo filtro por defecto - sólo artículos públicos.
            ->withFilter(new Equals('public', '1'));
            // la condición `public` = "1" no reemplaza `user_id` = "$userId"
    }
}
```
