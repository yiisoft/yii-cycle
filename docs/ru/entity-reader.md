# EntityReader

`EntityReader` является полезным инструментом для безопасной передачи select-запросов
из репозитория в пользовательскую среду выполнения.
Под select-запросом подразумевается экземпляр одного из
классов: `\Cycle\ORM\Select` или `\Cycle\Database\Query\SelectQuery`.

Что нужно знать о `EntityReader`:

* Класс `EntityReader` реализует интерфейс `IteratorAggregate`.
 Это позволяет использовать объект `EntityReader` в цикле `foreach`.
* С помощью `EntityReader` вы можете корректировать переданный select-запрос:
  - Выставлять `Limit` и `Offset` вручную или с помощью `OffsetPaginator`.
  - Задавать сортировку. Но учтите, что сортировка `EntityReader`
    не заменяет сортировку в исходном запросе, а лишь дополняет её.
    Однако каждый следующий вызов метода `withSort()` будет заменять настройки
    сортировки объекта `EntityReader`.
  - Применять фильтр. Условия фильтрации `EntityReader` также не заменяют настройки
    фильтрации в исходном запросе, а дополняют её. Таким образом, фильтрацией
    в объекте `EntityReader` вы можете только уточнить выборку, но не расширить.
* `EntityReader` не вытягивает данные из БД сразу.
  Обращение к БД происходит только тогда, когда эти данные запрашиваются.
* Если вы будете использовать методы `read()` и `readOne()` для чтения данных,
  то `EntityReader` сохранит их в кеше. Кешируется также и результат вызова `count()`.
* Метод `count()` возвращает кол-во всех элементов выборки без учёта ограничений `Limit` и `Offset`.
* Если вы не хотите, чтобы данные были записаны в кеш, то используйте метод `getIterator()`.
  Однако если кеш уже заполнен, то `getIterator()` вернёт содержимое кеша.

## Примеры

Напишем свой репозиторий для работы с таблицей статей, в котором будет метод для получения
списка публичных статей `findPublic()`. Но метод `findPublic()` не будет
возвращать сразу готовую коллекцию статей или select-запрос.\
Вместо этого будет возвращаться `EntityReader` с select-запросом внутри:

```php
use Yiisoft\Data\Reader\DataReaderInterface;
use \Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

class ArticleRepository extends \Cycle\ORM\Select\Repository
{
    public function findPublic(): DataReaderInterface
    {
        return new EntityReader($this->select()->where(['public' => true]));
    }
}
```
Рассмотрим примеры, как мы можем использовать `EntityReader` в постраничной разбивке.

```php
/**
 * @var ArticleRepository $repository
 * @var \Yiisoft\Yii\Cycle\Data\Reader\EntityReader $articles
 */
$articles = $repository->findPublic();

// Смещение и лимит можно задать вручную
// Третья страница:
$pageReader = $articles->withLimit(10)->withOffset(20);

// Использование пагинатора
$paginator = new \Yiisoft\Data\Paginator\OffsetPaginator($articles);
$paginator->withPageSize(10)->withCurrentPage(3);


// Обход статей из объекта EntityReader:
// С сохранением в кеше:
foreach ($pageReader->read() as $article) {
    // ...
}
// Без сохранения в кеше:
foreach ($pageReader as $article) {
    // ...
}

// Обход статей из пагинатора:
foreach ($paginator->read() as $article) {
    // ...
}
```

А теперь сделаем запрос на 20 последних опубликованных статей, а потом на 20 первых.

```php
/**
 * @var \Yiisoft\Yii\Cycle\Data\Reader\EntityReader $articles
 */

// Порядок указания параметров не важен, так что начнём с установки лимита
$lastPublicReader = $articles->withLimit(20);

// Правила сортировки описываются в объекте класса Sort:
$sort = \Yiisoft\Data\Reader\Sort::any()->withOrder(['published_at' => 'desc']);
// Учтите, что EntityReader НЕ БУДЕТ проверять правильность указанных в Sort полей!
// Указание несуществующих в таблице полей приведёт к ошибке в коде Cycle

// Применяем правила сортировки и не забываем об иммутабельности
$lastPublicReader = $lastPublicReader->withSort($sort);

printf(
    "Последние %d опубликованных статей из %d:",
    count($lastPublicReader->read()),
    $lastPublicReader->count()
);
foreach ($lastPublicReader->read() as $article) {
    // ...
}

// Теперь получим 20 первых опубликованных статей
$sort = $lastPublicReader->getSort()->withOrder(['published_at' => 'asc']);

// Ввиду своей иммутабельности, запрошенный объект Sort не будет изменён,
// и текущие настройки сортировки $lastPublicReader останутся без изменения.
// Для того, чтобы применить новые правила сортировки нужно снова вызвать метод withSort():
$lastPublicReader = $lastPublicReader->withSort($sort);

printf(
    "Первые %d опубликованных статей из %d:",
    count($lastPublicReader->read()),
    $lastPublicReader->count()
);
foreach ($lastPublicReader->read() as $article) {
    // ...
}
```

Одна из особенностей сортировки запроса через `EntityReader` заключается в том, что
она не заменяет сортировку в исходном select-запросе, а лишь дополняет её. \
Бывает так, что нужно задать сортировку по умолчанию в методе репозитория, но при этом
иметь возможность изменить её в коде контроллера. Добиться этого можно следующим образом:

```php
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

class ArticleRepository extends \Cycle\ORM\Select\Repository
{
    public function findPublic(): DataReaderInterface
    {
        $sort = Sort::any()->withOrder(['published_at' => 'desc']);
        // Параметры сортировки присваиваются объекту DataReader, а не \Cycle\ORM\Select
        return (new EntityReader($this->select()->where(['public' => true])))->withSort($sort);
    }
}

// class SiteController ... {

function index(ArticleRepository $repository)
{
    $articlesReader = $repository
        // Получаем объект EntityReader
        ->findPublic()
        // Применяем новое правило сортировки
        ->withSort(Sort::any()->withOrder(['published_at' => 'asc']));
}
```

Уточнить условия выборки можно с помощью фильтров. Они также дополняют условия выборки запроса, а не заменяют их.

```php
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

class ArticleRepository extends \Cycle\ORM\Select\Repository
{
    public function findUserArticles(int $userId): DataReaderInterface
    {
        return (new EntityReader($this->select()->where('user_id', $userId)))
            // Добавим фильтр по умолчанию - только public статьи
            ->withFilter(new Equals('public', '1'));
        // Условие `public` = "1" не заменит `user_id` = "$userId"
    }
}
```

Используйте фильтры пакета [yiisoft/data](https://github.com/yiisoft/data), либо любые другие, предварительно написав
для них соответствующие обработчики (процессоры).
