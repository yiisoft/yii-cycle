# SelectDataReader

`SelectDataReader` allows to securely pass select-queries from repository to user runtime.
By select-query we assume an instance of `\Cycle\ORM\Select` or `\Spiral\Database\Query\SelectQuery`.

You need to know the following about `SelectDataReader`:

* `SelectDataReader` implements `IteratorAggregate`.
 It allows using `SelectDataReader` instance in `foreach`.
* Using `SelectDataReader` you can adjust select-query:
  - Add `Limit` and `Offset` manually or using `OffsetPaginator`
  - Specify sorting. But note that `SelectDataReader` sorting does
    not replace initial query sorting but adds sorting on top of it.
    Each next `withSort()` call is replacing `SelectDataReader` sorting options.
* `SelectDataReader` doesn't allow filtering. It should be done in repository instead.
* `SelectDataReader` queries database only when you actually read the data.
* In case you're using `read()` to read data, data will be cached by `SelectDataReader`. Result of `count()` call is
  cached as well.
* In case you want to avoid caching, use `getIterator()`. But note that if cache is already there, `getIterator()`
  uses it.

## Examples

Let's implement a repository to work with articles table. We want a method to get public articles `findPublic()` but
it would not return ready articles collection or select query. Instead, it will return `SelectDataReader`:

```php
use Yiisoft\Data\Reader\DataReaderInterface;
use \Yiisoft\Yii\Cycle\DataReader\SelectDataReader;

class ArticleRepository extends \Cycle\ORM\Select\Repository
{
    /**
     * @return SelectDataReader
     */
    public function findPublic(): DataReaderInterface
    {
        return new SelectDataReader($this->select()->where(['public' => true]));
    }
}
```

Now we can use `SelectDataReader` for pagination like the following:

```php
/**
 * @var ArticleRepository $repository
 * @var \Yiisoft\Yii\Cycle\DataReader\SelectDataReader $articles
 */
$articles = $repository->findPublic();

// Offset and limit could be specified explicitly.
// Third page:
$pageReader = $articles->withLimit(10)->withOffset(20);

// Paginator could be used instead.
$paginator = new \Yiisoft\Data\Paginator\OffsetPaginator($articles);
$paginator->withPageSize(10)->withCurrentPage(3);


// Getting articles from SelectDataReader with caching:
foreach ($pageReader->read() as $article) {
    // ...
}
// Same without caching:
foreach ($pageReader as $article) {
    // ...
}

// Getting articles from paginator:
foreach ($paginator->read() as $article) {
    // ...
}
```

Now we'll query for 20 latest published articles, then for 20 first articles.

```php
/**
 * @var \Yiisoft\Yii\Cycle\DataReader\SelectDataReader $articles
 */

// The order of specifying parameters is not important so let's start with limit
$lastPublicReader = $articles->withLimit(20);

// Ordering is specified with Sort object:
$sort = (new \Yiisoft\Data\Reader\Sort([]))->withOrder(['published_at' => 'desc']);
// Note that neither Sort not SelectDataReader would not check field correctness.
// Specifying non-existing fields would result in an error in Cycle code

// Don't forget about immutability when applying sorting rules
$lastPublicReader = $lastPublicReader->withSort($sort);

printf(
    "Last %d published articles of %d total:",
    count($lastPublicReader->read()),
    $lastPublicReader->count()
);
foreach ($lastPublicReader->read() as $article) {
    // ...
}

// Now let's obtain 20 first published articles
$sort = $lastPublicReader->getSort()->withOrder(['published_at' => 'asc']);

// Because of immutability Sort object won't be modified and current 
// sorting for $lastPublicReader will stay the same.
// To apply new sorting rules call withSort() once again:
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

Sorting through `SelectDataReader` does not replace sorting in initial query but adds more to it.
If you need to set default sorting in a repository method but want to be able to change it in controller, you
can do it like the following:

```php
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\DataReader\SelectDataReader;

class ArticleRepository extends \Cycle\ORM\Select\Repository
{
    /**
     * @return SelectDataReader
     */
    public function findPublic(): DataReaderInterface
    {
        $sort = (new Sort([]))->withOrder(['published_at' => 'desc']);
        return (new SelectDataReader($this->select()->where(['public' => true])))->withSort($sort);
    }
}

// class SiteController ... {

function index(\Cycle\ORM\ORMInterface $orm)
{
    /** @var ArticleRepository $repository */
    $repository = $orm->getRepository(Article::class);

    $articlesReader = $repository
        // getting SelectDataReader
        ->findPublic()
        // applying new sorting
        ->withSort((new Sort([]))->withOrder(['published_at' => 'asc']));
}
```
