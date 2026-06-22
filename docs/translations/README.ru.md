<p align="center">
  <a href="README.ar.md">العربية</a> •
  <a href="README.de.md">Deutsch</a> •
  <a href="../../README.md">English</a> •
  <a href="README.es.md">Español</a> •
  <a href="README.fr.md">Français</a> •
  <a href="README.it.md">Italiano</a> •
  <a href="README.ja.md">日本語</a> •
  <a href="README.ko.md">한국어</a> •
  <a href="README.nl.md">Nederlands</a> •
  <a href="README.pl.md">Polski</a> •
  <a href="README.pt-BR.md">Português (BR)</a> •
  <b>Русский</b> •
  <a href="README.tr.md">Türkçe</a> •
  <a href="README.zh-CN.md">简体中文</a>
</p>

<p align="center">
  <a href="https://saddlephp.com"><img src=".github/og.png" alt="Saddle, there's a new admin panel in town" width="820"></a>
</p>

<p align="center">
  <em>Седлай коня, ковбой, в городе появилась новая панель администрирования.</em>
</p>

---

**Saddle** это фреймворк панели администрирования с открытым исходным кодом для Laravel, созданный современным способом для **Inertia and
Vue**. Соберите модели Eloquent в аккуратные панели ресурсов, с конструкторами форм и таблиц, ролями и доступом,
плагинами и мультитенантностью.

> **Статус: v1.0, импорт/экспорт CSV и дополнительные возможности тенантности.** Маркетинговый сайт находится на **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## Установка

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Service provider обнаруживается автоматически. `saddle:install` публикует файл конфигурации, публикует ассеты панели и создает `app/Saddle/` для ваших классов ресурсов. Откройте `/admin`, чтобы увидеть панель.

## Определение ресурса

Размещайте классы ресурсов в `app/Saddle/`. Каждый класс расширяет `SaddlePHP\Resource` и реализует `form()` и `table()`.

```php
<?php

declare(strict_types=1);

namespace App\Saddle;

use App\Models\Horse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use SaddlePHP\Actions\Action;
use SaddlePHP\Actions\BulkAction;
use SaddlePHP\Fields\BelongsTo;
use SaddlePHP\Fields\Date;
use SaddlePHP\Fields\DateTime;
use SaddlePHP\Fields\FileUpload;
use SaddlePHP\Fields\Markdown;
use SaddlePHP\Fields\Number;
use SaddlePHP\Fields\Select;
use SaddlePHP\Fields\Text;
use SaddlePHP\Fields\Toggle;
use SaddlePHP\Forms\Form;
use SaddlePHP\Forms\Layout\Grid;
use SaddlePHP\Forms\Layout\Section;
use SaddlePHP\Forms\Layout\Tab;
use SaddlePHP\Forms\Layout\Tabs;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\BadgeColumn;
use SaddlePHP\Tables\Columns\BooleanColumn;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Filters\BooleanFilter;
use SaddlePHP\Tables\Filters\SelectFilter;
use SaddlePHP\Tables\Table;

class HorseResource extends Resource
{
    public static string $model = Horse::class;

    public static ?string $title = 'name';

    public static ?string $icon = 'collection';

    public static array $with = ['rider'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identity')->description('Who this horse is on the ranch.')->schema([
                Grid::make(2)->schema([
                    Text::make('name')->required()->rules('max:120'),
                    Select::make('breed')->options([
                        'quarter' => 'Quarter Horse',
                        'mustang' => 'Mustang',
                        'appaloosa' => 'Appaloosa',
                    ]),
                ]),
                FileUpload::make('photo')->image()->directory('horses')->maxSize(4096),
            ]),
            Tabs::make([
                Tab::make('Care')->schema([
                    Markdown::make('notes')
                        ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
                    DateTime::make('last_vet_visit'),
                    Number::make('age')->integer()->min(0)->max(50),
                    Date::make('foaled_on'),
                ]),
                Tab::make('Assignment')->schema([
                    BelongsTo::make('rider')->searchable(),
                    Toggle::make('is_saddled'),
                ]),
            ]),
        ]);
    }

    public static function actions(): array
    {
        return [
            Action::make('unsaddle')
                ->handle(fn (Horse $horse) => $horse->update(['is_saddled' => false]))
                ->requiresConfirmation('Unsaddle this horse?')
                ->color('accent'),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            BulkAction::make('saddle-up')
                ->label('Saddle up')
                ->handle(fn (Collection $horses) => $horses->each->update(['is_saddled' => true])),
            BulkAction::delete(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->sortable()->searchable(),
            BadgeColumn::make('breed')->colors([
                'quarter' => 'accent',
                'mustang' => 'ink',
                'appaloosa' => 'muted',
            ]),
            BooleanColumn::make('is_saddled'),
            TextColumn::make('rider.name')->label('Rider'),
            TextColumn::make('created_at')->date('M j, Y')->sortable(),
        ])->filters([
            SelectFilter::make('breed')->options([
                'quarter' => 'Quarter Horse',
                'mustang' => 'Mustang',
                'appaloosa' => 'Appaloosa',
            ]),
            BooleanFilter::make('is_saddled'),
        ]);
    }
}
```

Ресурсы обнаруживаются автоматически при сканировании `app/Saddle/` во время запуска, ручная регистрация не нужна.

> **Зарезервированные ключи маршрутов.** Панель владеет статическими сегментами пути `create`, `options` и `actions` внутри каждого ресурса, поэтому запись, чей ключ маршрута буквально является одним из этих слов, недоступна через свои URL редактирования/обновления/удаления. Для таких записей используйте целочисленный ключ или другой slug.

## Макет формы

Поля можно группировать в контейнеры макета. Контейнеры могут свободно вкладываться друг в друга.

```php
use SaddlePHP\Forms\Layout\Grid;
use SaddlePHP\Forms\Layout\Section;
use SaddlePHP\Forms\Layout\Tab;
use SaddlePHP\Forms\Layout\Tabs;

$form->schema([
    Section::make('Identity')->description('Who this horse is on the ranch.')->schema([
        Grid::make(2)->schema([
            Text::make('name')->required(),
            Select::make('breed')->options([...]),
        ]),
        FileUpload::make('photo')->image()->directory('horses')->maxSize(4096),
    ]),
    Tabs::make([
        Tab::make('Care')->schema([
            Markdown::make('notes'),
            DateTime::make('last_vet_visit'),
        ]),
        Tab::make('Assignment')->schema([
            BelongsTo::make('rider')->searchable(),
            Toggle::make('is_saddled'),
        ]),
    ]),
]);
```

| Контейнер | Описание |
|---|---|
| `Section` | Группа карточек с подписью. `description(string)` добавляет подзаголовок. Принимает `schema([...])` из полей и вложенных контейнеров. |
| `Grid` | Раскладывает дочерние элементы в CSS grid. `Grid::make(2)` создает сетку из двух колонок. Поля внутри Grid используют `columnSpan(int)`, чтобы занимать несколько колонок. |
| `Tabs` | Оборачивает один или несколько контейнеров `Tab` в интерфейс с вкладками. |
| `Tab` | Отдельная панель внутри группы `Tabs`. `Tab::make('Label')->schema([...])`. Когда любое поле внутри вкладки не проходит валидацию, вкладка показывает индикатор ошибки, чтобы пользователи могли найти проблему без перехода на каждую панель. |

**Плоские схемы по-прежнему работают.** Передача обычного списка полей в `$form->schema([...])` без контейнеров полностью поддерживается и дает тот же макет, что и раньше. Валидация и авторизация обрабатывают поля одинаково независимо от того, находятся ли они внутри контейнера или на верхнем уровне.

## Поля

| Поле | Описание |
|---|---|
| `Text` | Однострочный текстовый ввод. Модификаторы: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | Многострочный текстовый ввод. Модификаторы: `rows(int)`. |
| `Select` | Выпадающий список с фиксированными опциями. Передайте ассоциативный массив в `options(['value' => 'Label'])`. |
| `Toggle` | Булев переключатель. Сохраняет `true`/`false`. |
| `BelongsTo` | Выбор отношения. Аргументом является имя метода отношения Eloquent на модели (`BelongsTo::make('rider')` читает `$model->rider()` и отправляет внешний ключ). Подписи опций разрешаются из `titleAttribute('name')`, затем с fallback на зарегистрированный ресурс `$title` связанной модели, а затем на ее ключ. Если нет ни `titleAttribute()`, ни зарегистрированного ресурса, опции подписываются первичным ключом, поэтому задайте `titleAttribute('name')` для читаемых подписей. По умолчанию опции ограничены 100, переопределите через `limit(int)`. `searchable()` переключает на асинхронный picker, который ищет в связанной таблице по мере ввода через аутентифицированный endpoint. При редактировании встраивается только текущий выбор, полный список не загружается. `modifyOptionsQuery(fn ($query) => ...)` ограничивает список опций для тенантности или видимости. Это применяется к перечисленным и найденным опциям, а сохраненный выбор записи продолжает показывать свою подпись, даже если он выходит за пределы области. |
| `Number` | Числовой ввод. Модификаторы: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | Ввод даты. Значения отображаются как `Y-m-d`. |
| `DateTime` | Ввод даты и времени (`datetime-local`). Значения сохраняются и читаются обратно через datetime cast вашей модели. Значение `DateTimeInterface` форматируется для браузера как `Y-m-d\TH:i`. |
| `Markdown` | Textarea с панелью форматирования. Сохраняется как обычная строка и ограничивается 65 535 символами, что соответствует колонке MySQL `TEXT`. |
| `FileUpload` | Multipart загрузка файла. Модификаторы: `disk(string)`, `directory(string)`, `image()` (ограничивает типами изображений), `acceptedTypes(array)` (расширения MIME, например `['pdf', 'docx']`), `maxSize(int $kilobytes)`. Сохраненное значение это путь к файлу, возвращенный `Storage::put`. В форме редактирования: если не трогать ввод, существующий файл сохраняется, очистка сохраняет `null`, а выбор нового файла заменяет путь. Замененные или очищенные файлы не удаляются с диска автоматически. |

## Колонки

| Колонка | Описание |
|---|---|
| `TextColumn` | Отображает сырое значение атрибута. Модификаторы: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (форматирует атрибуты DateTime, формат по умолчанию `Y-m-d H:i`). |
| `BadgeColumn` | Отображает badge-плашку. Используйте `colors(['value' => 'token'])`, чтобы сопоставить значения опций с цветовыми токенами (`accent`, `ink`, `muted`). |
| `BooleanColumn` | Отображает галочку для truthy значений и тире для falsy значений. |

**Колонки отношений и eager loading.** Имена с точками, такие как `TextColumn::make('rider.name')`, читают через загруженное отношение. Объявите `public static array $with = ['rider']` на ресурсе, чтобы индексный запрос eager-load отношение перед отображением. Колонки отношений пока не сортируются и не ищутся.

## Фильтры

Фильтры объявляются на таблице через `->filters([...])`. На индексе панель применяет их из параметров query string `filter[name]=value`. Запрошенные значения валидируются по объявлению, поэтому неизвестные имена фильтров и необъявленные значения опций тихо игнорируются.

| Фильтр | Описание |
|---|---|
| `SelectFilter` | Выпадающий список точного совпадения. `options(['value' => 'Label'])` задает и варианты выпадающего списка, и allowlist допустимых значений. |
| `BooleanFilter` | Выпадающий список Да/Нет по булевой колонке. |

## Действия

Действия появляются как кнопки в каждой строке индексной таблицы. Массовые действия появляются на панели инструментов, когда выбрана одна или несколько строк.

```php
use SaddlePHP\Actions\Action;
use SaddlePHP\Actions\BulkAction;

public static function actions(): array
{
    return [
        Action::make('unsaddle')
            ->handle(fn (Horse $horse) => $horse->update(['is_saddled' => false]))
            ->requiresConfirmation('Unsaddle this horse?')
            ->color('accent'),
    ];
}

public static function bulkActions(): array
{
    return [
        BulkAction::make('saddle-up')
            ->label('Saddle up')
            ->handle(fn (Collection $horses) => $horses->each->update(['is_saddled' => true])),
        BulkAction::delete(),
    ];
}
```

| Fluent | Описание |
|---|---|
| `label(string)` | Отображаемая подпись на кнопке. Если опущена, имя преобразуется в title case. |
| `color(string)` | Цветовой токен для кнопки: `accent`, `ink` или `muted`. По умолчанию `ink`. |
| `requiresConfirmation(?string)` | Показывает диалог подтверждения перед запуском. Передайте свое сообщение или опустите, чтобы использовать стандартный prompt. |
| `authorize(string)` | Называет способность политики, проверяемую для каждой записи перед запуском handler. |
| `successMessage(string)` | Flash-сообщение, показываемое после успешного запуска. По умолчанию `Done.`. |

Действия отправляют POST на защищенный endpoint. Записи разрешаются через тот же scoped базовый запрос, который используется везде, поэтому тенантность, фильтры и query scopes ресурсов применяются автоматически. Когда объявлено `authorize('ability')`, политика проверяется для каждой записи перед запуском handler. Массовые запуски выполняются внутри транзакции базы данных и ограничены 100 записями на запрос. Если какая-либо запрошенная запись отсутствует в scoped выборке, вся операция прерывается с 404 вместо тихого применения к разрешенному подмножеству. Объявляйте `authorize()` на любом разрушительном действии.

`BulkAction::delete()` это готовый preset: имя `delete`, подпись `Delete`, цвет `accent`, подтверждение `Delete the selected records?`, и `authorize('delete')` уже подключен.

## Авторизация

Saddle использует стандартные политики Laravel. Зарегистрируйте политику для модели, и панель будет применять ее везде: индекс, формы, действия строк и pickers отношений. Если политика не зарегистрирована, все способности разрешены каждому аутентифицированному пользователю. Роли остаются в вашем приложении: любой пакет ролей или самописный слой, на который опираются ваши политики, работает без изменений.

### Закрыть панель

Ресурсы без зарегистрированной политики по умолчанию разрешают каждого аутентифицированного пользователя. Установите `saddle.authorization.require_policy` в `true`, чтобы fail closed: ресурсы без политики станут недоступны, а не открыты. Можно также добавить gate middleware в `saddle.middleware` для общей проверки перед запуском любого маршрута панели. Если ваш web guard общий для конечных пользователей и администраторов, один из этих механизмов обязателен.

| Способность | Где проверяется |
|---|---|
| `viewAny` | Индексная страница ресурса, видимость в боковой панели |
| `create` | Форма создания, действие store, endpoint опций отношения |
| `update` | Форма редактирования, действие update, ссылка Edit для строки, endpoint опций отношения (проверяется против новой модели, когда в области нет записи) |
| `delete` | Действие destroy, кнопка Delete для строки |

### Видимость полей с `canSee`

Отдельные поля можно ограничивать по запросу с помощью `canSee`. Поле `notes` в `HorseResource` является рабочим примером:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

Скрытые поля удаляются из payload формы (сохраненные значения никогда не сериализуются во frontend), не добавляют правил валидации, никогда не записываются при сохранении, а их endpoint опций отношения возвращает 404. Callback может выполняться несколько раз за запрос, поэтому держите его дешевым и возвращайте настоящий boolean. Например, используйте `Gate::allows('view-notes', $model)` вместо `Gate::inspect(...)`, чей объект `Response` всегда truthy и никогда не скроет поле.

## Плагины

Плагин это обычный пакет Composer. Его service provider регистрирует ресурсы, скрипты и стили через фасад `Saddle`, а package auto-discovery Laravel автоматически запускает его вместе с вашим приложением.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

Опубликуйте скомпилированные ассеты из service provider плагина в `public/vendor/{plugin}` с помощью стандартного механизма `$this->publishes([...])`, затем укажите `Saddle::script()` на опубликованный путь. Скрипты и таблицы стилей плагина загружаются на каждой странице панели после основного bundle панели.

### Пользовательские элементы

Плагины могут поставлять собственные рендереры полей и колонок как пользовательские элементы. На стороне PHP:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

Панель выполняет этот контракт: для полей она задает DOM-свойства элемента `value` и `field` и слушает CustomEvent `saddle:input`, чей `detail` является новым значением. Для колонок она задает DOM-свойства `value` и `column` (только чтение, событие ввода не ожидается).

Минимальный vanilla пользовательский элемент, реализующий контракт поля:

```js
class MoodPicker extends HTMLElement {
    connectedCallback() {
        // The panel may set the value property before the element is
        // connected, so seed the input from whatever arrived early.
        this._input = document.createElement('input');
        this._input.value = this._value ?? '';
        this._input.addEventListener('input', () => {
            this.dispatchEvent(new CustomEvent('saddle:input', {
                bubbles: true,
                detail: this._input.value.toUpperCase(),
            }));
        });
        this.appendChild(this._input);
    }

    set value(v) {
        this._value = v ?? '';
        if (this._input) this._input.value = this._value;
    }

    get value() { return this._input ? this._input.value : (this._value ?? ''); }
}

customElements.define('mood-picker', MoodPicker);
```

Определяйте элементы на верхнем уровне вашего скрипта. Браузер обновляет любые подходящие элементы, которые панель уже отрендерила, как только запускается `customElements.define`, поэтому порядок загрузки не имеет значения.

Контракт не зависит от framework. Работает все, что компилируется в стандартный пользовательский элемент: `defineCustomElement` из Vue, Lit, обертки React или Svelte. Авторы плагинов не привязаны к внутренностям панели.

## Мультитенантность

Saddle поддерживает опциональную мультитенантность со scope в URL. Включите ее, указав `tenancy.model` на любой класс Eloquent:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

Когда тенантность активна, панель монтируется под `/admin/{tenant}` вместо `/admin`. Сегмент `{tenant}` разрешается lookup по route-key на настроенной модели. Неизвестные ключи тенантов возвращают **404**. Аутентифицированные пользователи, которые не являются участниками разрешенного тенанта, отклоняются с **403**.

### Ограничение ресурсов

Объявите BelongsTo отношение записи к тенанту на каждом ресурсе, который хотите ограничить:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

Ресурсы без `$tenant` (общие справочные таблицы, глобальная конфигурация) намеренно остаются без scope.

### Гарантии автоматического scope

Каждый путь данных проверяет привязанного тенанта на стороне сервера:

- **Индекс, поиск и фильтры** проходят через scoped базовый запрос (`whereBelongsTo` на объявленном отношении).
- **Record lookups** для редактирования, обновления и уничтожения разрешаются через тот же scoped запрос, поэтому cross-tenant IDs возвращают 404 до запуска любой политики.
- **Stores** проставляют текущего тенанта на стороне сервера после заполнения формы. Любой внешний ключ тенанта, отправленный клиентом, перезаписывается.
- **Списки опций отношений** применяют тот же scope, когда зарегистрированный ресурс связанной модели также tenant-scoped.

### Переключатель тенанта

Когда аутентифицированный пользователь принадлежит более чем одному тенанту, боковая панель показывает select со списком всех его участий. Переключение ведет на тот же путь панели под выбранным тенантом.

### Оговорки

- **Не раскрывайте отношение `$tenant` как поле формы на scoped ресурсе.** Контроллер store проставляет отношение на стороне сервера, но редактируемое поле BelongsTo, указывающее на отношение тенанта в форме update, позволило бы отправленному значению переназначить запись на другого тенанта.
- **Сохраненная подпись отношения все равно отображается в форме редактирования, даже когда связанная строка выходит за пределы текущего scope.** `BelongsTo` разрешает текущий выбор запросом без scope, чтобы подпись не исчезала после изменения scope. Фильтруется только список опций для новых выборов.
- **Изменение конфигурации тенантности требует `php artisan route:clear`**, потому что префикс `{tenant}` определяется при запуске. Долго живущие серверы приложений (FPM workers, сохраняющиеся между запросами) должны гарантировать сброс состояния запроса между запросами. Привязанный тенант живет на singleton Saddle, который заново разрешается на каждый запрос при стандартном времени жизни контейнера. Когда Octane установлен, панель автоматически сбрасывает привязанного тенанта через hooks жизненного цикла запросов Octane.

## Конфигурация

`saddle:install` публикует `config/saddle.php`. Доступные ключи:

| Ключ | По умолчанию | Описание |
|---|---|---|
| `path` | `'admin'` | URL-префикс панели (например `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Стек middleware, применяемый ко всем маршрутам панели. |
| `resources.path` | `app_path('Saddle')` | Путь файловой системы, сканируемый для классов ресурсов. |
| `resources.namespace` | `'App\\Saddle'` | PHP namespace, соответствующий `resources.path`. |
| `per_page` | `25` | Строк по умолчанию на страницу в индексных таблицах. |
| `brand.name` | `'Saddle'` | Имя панели (боковая панель и вкладка браузера). |
| `brand.accent` | `'#d9501f'` | Акцентный цвет (кнопки, активные состояния). |
| `uploads.disk` | `'public'` | Файловый диск по умолчанию, используемый полями `FileUpload`, когда не задан `disk()` для поля. |
| `uploads.directory` | `'saddle'` | Директория загрузки по умолчанию внутри диска, когда не задан `directory()` для поля. |

## Команды

| Команда | Описание |
|---|---|
| `saddle:install` | Публикует конфигурацию, публикует ассеты панели, создает `app/Saddle/`. Предлагает добавить `saddle:upgrade` в `composer post-update-cmd`, чтобы ассеты оставались свежими. |
| `saddle:upgrade` | Повторно публикует ассеты панели. Запускайте после каждого обновления пакета. |
| `saddle:resource NameResource --model=Name` | Создает каркас нового класса ресурса. Опция `--model` необязательна. Если она опущена, она выводится из имени ресурса. |

**Примечание к деплою.** Добавьте `php artisan saddle:upgrade` в ваш скрипт деплоя после `composer install` или `composer update`. Панель показывает предупреждающий баннер в UI, когда опубликованные ассеты не синхронизированы с установленной версией пакета.

## Локальная разработка

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

Директория `workbench/` содержит минимальное host-приложение, используемое набором тестов и для ручных проверок. `vendor/bin/testbench serve` запускает его с зарегистрированным `HorseResource`. Учтите, что маршруты панели находятся за middleware `auth`, а workbench содержит только stub-маршрут `/login`, поэтому для интерактивного просмотра временно установите `'middleware' => ['web']` в `config/saddle.php` или вместо этого проходите через feature tests. Demo seeder пока нет.

## Стек

Создано для **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## Лицензия

MIT.
