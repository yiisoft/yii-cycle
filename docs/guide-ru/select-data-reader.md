# SelectDataReader

`SelectDataReader` является полезным инструментом для безопасной передачи select-запросов
из репозитория в пользовательскую среду выполнения.
Под select-запросом подразумевается сущность одного из
классов: `\Cycle\ORM\Select` или `\Spiral\Database\Query\SelectQuery`.

Что нужно знать о `SelectDataReader`:

* Класс `SelectDataReader` реализует интерфейс `IteratorAggregate`.
 Это позволяет использовать объект `SelectDataReader` в цикле `foreach`.
* С помощью `SelectDataReader` вы можете корректировать переданный select-запрос:
  - Выставлять `Limit` и `Offset` вручную или с помощью `OffsetPaginator`
  - Задавать сортировку. Но учтите, что сортировка `SelectDataReader`
    не заменяет сортировку в исходном запросе, а лишь дополняет её.
    Однако последовательный вызов метода `withSort()` будет заменять настройки
    сортровки объекта `SelectDataReader`
* `SelectDataReader` не позволяет применять фильтрацию — её следует настраивать в репозитории.
* `SelectDataReader` не вытягивает данные из БД сразу.
  Обращение к БД происходит только тогда, когда эти данные запрашиваются.
* Если вы будете использовать метод `read()` для чтения данных,
  то `SelectDataReader` сохранит их в кеше. Кешируется также и результат вызова `count()`.
* Если вы не хотите, чтобы данные были записаны в кеш, то используйте метод `getIterator()`.
  Однако, если кеш уже заполнен, то `getIterator()` вернёт содержимое кеша.

## Примеры

Напишем свой репозиторий для работы с таблицей статей, в котором будет метод для получения
списка публичных статей `findPublic()`. Но метод `findPublic()` не будет
возвращать сразу готовую коллекцию статей или select-запрос.\
Вместо этого будет возвращаться `SelectDataReader` с select-запросом внутри:

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
Рассмотрим примеры, как мы можем использовать SelectDataReader в постраничной разбивке.
```php
/**
 * @var ArticleRepository $repository
 * @var \Yiisoft\Yii\Cycle\DataReader\SelectDataReader $articles
 */
$articles = $repository->findPublic();

// Смещение и лимит можно задать вручную
// Третья страница:
$pageReader = $articles->withLimit(10)->withOffset(20);

// Использование пагинатора
$paginator = new \Yiisoft\Data\Paginator\OffsetPaginator($articles);
$paginator->withPageSize(10)->withCurrentPage(3);


// Обход статей из объекта SelectDataReader:
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

А тепрь сделаем запрос на 20 последних опубликованных статей

```php
/**
 * @var \Yiisoft\Yii\Cycle\DataReader\SelectDataReader $articles
 */

// Порядок указания параметров не важен, так что начнём с установки лимита
$lastPublicReader = $articles->withLimit(20);

// Правила сортировки описываются в объекте класса Sort:
$sort = (new \Yiisoft\Data\Reader\Sort([]))->withOrder(['published_at' => 'desc']);
// Учтите, что ни объект Sort, ни SelectDataReader НЕ БУДУТ проверять правильность
// указанных полей! Указание несуществующих полей приведёт к ошибке в коде Cycle

// Применяем правила сортировки и не забываем об иммутабельности
$lastPublicReader = $lastPublicReader->withSort($sort);

printf("Последние %d опубликованных статей:", $lastPublicReader->count());
foreach ($lastPublicReader->read() as $article) {
    // ...
}
```
