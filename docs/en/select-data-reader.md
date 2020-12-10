# SelectDataReader

`SelectDataReader` allows to securely pass select-queries from repository to user runtime.
By select-query we assume an instance of `\Cycle\ORM\Select` or `\Spiral\Database\Query\SelectQuery`.

You need to know the following about `SelectDataReader`:

* `SelectDataReader` implements `IteratorAggregate`.
 It allows using `SelectDataReader` instance in `foreach`.
* Using `SelectDataReader` you can adjust select-query:
  - Add `Limit` and `Offset` manually or using `OffsetPaginator`
  - Specify sorting. Note that `SelectDataReader` sorting does
    not replace initial query sorting but adds sorting on top of it.
    Each next `withSort()` call is replacing `SelectDataReader` sorting options.
  - Apply filter. Filtration conditions in `SelectDataReader` also, do not replace filtration conditions
    in initial query, but adds conditions on top of it. Therefore, by using filtration in `SeletecDataReader`
    you can only refine the selection, but NOT expand.
* `SelectDataReader` queries database only when you actually read the data.
* In case you're using `read()`, `readOne()` or `count()`, data will be cached by `SelectDataReader`.
* The `count()` method returns the number of elements without taking limit and offset into account.
* In case you want to avoid caching, use `getIterator()`. Note that if cache is already there, `getIterator()`
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
$sort = (new \Yiisoft\Data\Reader\Sort(['published_at']))->withOrder(['published_at' => 'desc']);
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
If you need to set default sorting in a repository method but want to be able to change it in a controller, you
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

function index(ArticleRepository $repository)
{
    $articlesReader = $repository
        // Getting SelectDataReader
        ->findPublic()
        // Applying new sorting
        ->withSort((new Sort([]))->withOrder(['published_at' => 'asc']));
}
```
You may refine query conditions with filters. This filtering conditions are adding to original select query conditions, but NOT replace them.


```php
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Yii\Cycle\DataReader\SelectDataReader;

class ArticleRepository extends \Cycle\ORM\Select\Repository
{
    public function findUserArticles(int $userId): DataReaderInterface
    {
        return (new SelectDataReader($this->select()->where('user_id', $userId)))
            //Adding filter by default - only public articles.
            
            ->withFilter(new Equals('public', '1'));
        // condition `public` = "1" doesnt replace `user_id` = "$userId"
    }
}
```

Use filters from  [yiisoft/data](https://github.com/yiisoft/data) package, or any others, having previously written
the appropriate handlers(processors) for them. 

