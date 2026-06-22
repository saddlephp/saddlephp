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
  <b>Polski</b> •
  <a href="README.pt-BR.md">Português (BR)</a> •
  <a href="README.ru.md">Русский</a> •
  <a href="README.tr.md">Türkçe</a> •
  <a href="README.zh-CN.md">简体中文</a>
</p>

<p align="center">
  <a href="https://saddlephp.com"><img src=".github/og.png" alt="Saddle, there's a new admin panel in town" width="820"></a>
</p>

<p align="center">
  <em>Siodłaj konia, kowboju, w mieście jest nowy panel administracyjny.</em>
</p>

---

**Saddle** to open-source'owy framework panelu administracyjnego dla Laravel, zbudowany nowocześnie dla **Inertia and
Vue**. Zbierz swoje modele Eloquent w dopracowane panele zasobów, z kreatorami formularzy i tabel, rolami i dostępem,
pluginami oraz wielodzierżawnością.

> **Status: v1.0, import/eksport CSV i dodatki dla wielodzierżawności.** Strona marketingowa znajduje się pod adresem **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## Instalacja

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Service provider jest wykrywany automatycznie. `saddle:install` publikuje plik konfiguracyjny, publikuje assety panelu i tworzy `app/Saddle/` dla klas zasobów. Odwiedź `/admin`, aby zobaczyć panel.

## Definiowanie zasobu

Umieszczaj klasy zasobów w `app/Saddle/`. Każda klasa rozszerza `SaddlePHP\Resource` i implementuje `form()` oraz `table()`.

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

Zasoby są wykrywane automatycznie przez skanowanie `app/Saddle/` podczas startu, bez potrzeby ręcznej rejestracji.

> **Zarezerwowane klucze tras.** Panel posiada statyczne segmenty ścieżki `create`, `options` i `actions` pod każdym zasobem, więc rekord, którego klucz trasy jest dosłownie jednym z tych słów, nie jest osiągalny przez swoje URL-e edycji/aktualizacji/usuwania. Dla takich rekordów użyj klucza całkowitego albo innego sluga.

## Układ formularza

Pola można grupować w kontenerach układu. Kontenery można swobodnie zagnieżdżać jedne w drugich.

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

| Kontener | Opis |
|---|---|
| `Section` | Oznaczona grupa kart. `description(string)` dodaje podtytuł. Przyjmuje `schema([...])` pól i zagnieżdżonych kontenerów. |
| `Grid` | Układa dzieci w siatce CSS. `Grid::make(2)` tworzy siatkę dwukolumnową. Pola wewnątrz Grid używają `columnSpan(int)`, aby rozciągać się na wiele kolumn. |
| `Tabs` | Opakowuje jeden lub więcej kontenerów `Tab` w interfejs z kartami. |
| `Tab` | Pojedynczy panel wewnątrz grupy `Tabs`. `Tab::make('Label')->schema([...])`. Gdy dowolne pole w karcie nie przejdzie walidacji, karta pokazuje wskaźnik błędu, aby użytkownicy mogli znaleźć problem bez przełączania się na każdy panel. |

**Płaskie schematy nadal działają.** Przekazanie zwykłej listy pól do `$form->schema([...])` bez kontenerów jest w pełni obsługiwane i tworzy taki sam układ jak wcześniej. Walidacja i autoryzacja traktują pola identycznie niezależnie od tego, czy znajdują się w kontenerze, czy na najwyższym poziomie.

## Pola

| Pole | Opis |
|---|---|
| `Text` | Jednowierszowe pole tekstowe. Modyfikatory: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | Wielowierszowe pole tekstowe. Modyfikatory: `rows(int)`. |
| `Select` | Lista rozwijana ze stałymi opcjami. Przekaż tablicę asocjacyjną do `options(['value' => 'Label'])`. |
| `Toggle` | Przełącznik boolowski. Zapisuje `true`/`false`. |
| `BelongsTo` | Wybór relacji. Argumentem jest nazwa metody relacji Eloquent na modelu (`BelongsTo::make('rider')` odczytuje `$model->rider()` i wysyła klucz obcy). Etykiety opcji są rozwiązywane z `titleAttribute('name')`, z fallbackiem do zarejestrowanego zasobu `$title` modelu powiązanego, a potem do jego klucza. Jeśli nie ma ani `titleAttribute()`, ani zarejestrowanego zasobu, opcje są etykietowane kluczem głównym, więc ustaw `titleAttribute('name')` dla czytelnych etykiet. Opcje są domyślnie ograniczone do 100; nadpisz to przez `limit(int)`. `searchable()` przełącza na asynchroniczny picker, który przeszukuje powiązaną tabelę podczas pisania przez uwierzytelniony endpoint. Przy edycji osadzony jest tylko bieżący wybór, pełna lista nie jest ładowana. `modifyOptionsQuery(fn ($query) => ...)` zawęża listę opcji dla wielodzierżawności lub widoczności. Dotyczy opcji listowanych i wyszukiwanych, a zapisany wybór rekordu nadal renderuje swoją etykietę nawet wtedy, gdy wypada poza zakres. |
| `Number` | Wejście numeryczne. Modyfikatory: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | Wejście daty. Wartości renderują się jako `Y-m-d`. |
| `DateTime` | Wejście daty i czasu (`datetime-local`). Wartości są zapisywane i odczytywane przez datetime cast twojego modelu. Wartość `DateTimeInterface` jest formatowana dla przeglądarki jako `Y-m-d\TH:i`. |
| `Markdown` | Textarea z paskiem formatowania. Przechowywana jako zwykły string i ograniczona do 65 535 znaków, co odpowiada kolumnie MySQL `TEXT`. |
| `FileUpload` | Multipart upload pliku. Modyfikatory: `disk(string)`, `directory(string)`, `image()` (ogranicza do typów obrazów), `acceptedTypes(array)` (rozszerzenia MIME, np. `['pdf', 'docx']`), `maxSize(int $kilobytes)`. Zapisana wartość to ścieżka pliku zwrócona przez `Storage::put`. W formularzu edycji: pozostawienie wejścia bez zmian zachowuje istniejący plik, wyczyszczenie zapisuje `null`, a wybranie nowego pliku zastępuje ścieżkę. Zastąpione lub wyczyszczone pliki nie są automatycznie usuwane z dysku. |

## Kolumny

| Kolumna | Opis |
|---|---|
| `TextColumn` | Renderuje surową wartość atrybutu. Modyfikatory: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (formatuje atrybuty DateTime, format domyślny `Y-m-d H:i`). |
| `BadgeColumn` | Renderuje badge w kształcie pigułki. Użyj `colors(['value' => 'token'])`, aby mapować wartości opcji na tokeny kolorów (`accent`, `ink`, `muted`). |
| `BooleanColumn` | Renderuje znacznik wyboru dla wartości truthy i kreskę dla wartości falsy. |

**Kolumny relacji i eager loading.** Nazwy z kropką, takie jak `TextColumn::make('rider.name')`, czytają przez załadowaną relację. Zadeklaruj `public static array $with = ['rider']` na zasobie, aby zapytanie indeksu załadowało relację przed renderowaniem. Kolumny relacji nie są jeszcze sortowalne ani wyszukiwalne.

## Filtry

Filtry są deklarowane na tabeli przez `->filters([...])`. Na indeksie panel stosuje je z parametrów query string `filter[name]=value`. Żądane wartości są walidowane względem deklaracji, więc nieznane nazwy filtrów i niezadeklarowane wartości opcji są cicho ignorowane.

| Filtr | Opis |
|---|---|
| `SelectFilter` | Lista rozwijana dokładnego dopasowania. `options(['value' => 'Label'])` definiuje zarówno wybory dropdownu, jak i allowlistę akceptowanych wartości. |
| `BooleanFilter` | Lista rozwijana Tak/Nie nad kolumną boolowską. |

## Akcje

Akcje pojawiają się jako przyciski w każdym wierszu tabeli indeksu. Akcje masowe pojawiają się w pasku narzędzi, gdy zaznaczony jest jeden lub więcej wierszy.

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

| Fluent | Opis |
|---|---|
| `label(string)` | Etykieta wyświetlana na przycisku. Gdy pominięta, nazwa jest konwertowana do title case. |
| `color(string)` | Token koloru przycisku: `accent`, `ink` albo `muted`. Domyślnie `ink`. |
| `requiresConfirmation(?string)` | Pokazuje dialog potwierdzenia przed uruchomieniem. Przekaż własną wiadomość albo pomiń, aby użyć domyślnego pytania. |
| `authorize(string)` | Nazywa zdolność polityki sprawdzaną per rekord przed uruchomieniem handlera. |
| `successMessage(string)` | Komunikat flash pokazywany po udanym uruchomieniu. Domyślnie `Done.`. |

Akcje wysyłają POST do chronionego endpointu. Rekordy są rozwiązywane przez to samo bazowe zapytanie z zakresem używane wszędzie indziej, więc wielodzierżawność, filtry i zakresy zapytań per zasób stosują się automatycznie. Gdy zadeklarowano `authorize('ability')`, polityka jest sprawdzana per rekord przed uruchomieniem handlera. Uruchomienia masowe wykonują się w transakcji bazy danych i są ograniczone do 100 rekordów na żądanie. Jeśli dowolny żądany rekord nie istnieje w pobraniu z zakresem, cała operacja przerywa się z 404 zamiast cicho zastosować się do rozwiązanego podzbioru. Deklaruj `authorize()` na każdej destrukcyjnej akcji.

`BulkAction::delete()` to gotowy preset: nazwa `delete`, etykieta `Delete`, kolor `accent`, potwierdzenie `Delete the selected records?` oraz `authorize('delete')` są już podłączone.

## Autoryzacja

Saddle używa standardowych polityk Laravel. Zarejestruj politykę dla modelu, a panel egzekwuje ją wszędzie: indeks, formularze, akcje wierszy i pickery relacji. Bez zarejestrowanej polityki wszystkie zdolności są dozwolone dla każdego uwierzytelnionego użytkownika. Role pozostają w twojej aplikacji: dowolny pakiet ról lub własna warstwa wspierająca polityki działa bez zmian.

### Zablokowanie panelu

Zasoby bez zarejestrowanej polityki domyślnie pozwalają każdemu uwierzytelnionemu użytkownikowi. Ustaw `saddle.authorization.require_policy` na `true`, aby zawodzić zamknięte: zasoby bez polityki stają się niedostępne zamiast otwarte. Możesz też dodać middleware gate do `saddle.middleware`, aby wykonać ogólną kontrolę przed każdą trasą panelu. Jeśli twój web guard jest współdzielony między użytkownikami końcowymi i administratorami, jedna z tych kontroli jest niezbędna.

| Zdolność | Gdzie jest sprawdzana |
|---|---|
| `viewAny` | Strona indeksu zasobu, widoczność paska bocznego |
| `create` | Formularz tworzenia, akcja store, endpoint opcji relacji |
| `update` | Formularz edycji, akcja update, link Edit per wiersz, endpoint opcji relacji (sprawdzane względem świeżego modelu, gdy żaden rekord nie jest w zakresie) |
| `delete` | Akcja destroy, przycisk Delete per wiersz |

### Widoczność pól z `canSee`

Pojedyncze pola można bramkować per żądanie za pomocą `canSee`. Pole `notes` w `HorseResource` jest działającym przykładem:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

Ukryte pola są usuwane z payloadu formularza (zapisane wartości nigdy nie są serializowane do frontendu), nie dodają reguł walidacji, nigdy nie są zapisywane przy zapisie, a ich endpoint opcji relacji zwraca 404. Callback może uruchamiać się kilka razy na żądanie, więc utrzymuj go tanim i zwracaj prawdziwy boolean. Na przykład użyj `Gate::allows('view-notes', $model)` zamiast `Gate::inspect(...)`, którego obiekt `Response` jest zawsze truthy i nigdy nie ukryje pola.

## Pluginy

Plugin jest zwykłym pakietem Composer. Jego service provider rejestruje zasoby, skrypty i style przez fasadę `Saddle`, a package auto-discovery Laravel uruchamia go automatycznie razem z twoją aplikacją.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

Opublikuj skompilowane assety z service providera pluginu do `public/vendor/{plugin}` przy użyciu standardowego mechanizmu `$this->publishes([...])`, a potem wskaż `Saddle::script()` na opublikowaną ścieżkę. Skrypty i arkusze stylów pluginów są ładowane na każdej stronie panelu po głównym bundle panelu.

### Elementy niestandardowe

Pluginy mogą dostarczać własne renderery pól i kolumn jako elementy niestandardowe. Po stronie PHP:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

Panel spełnia ten kontrakt: dla pól ustawia właściwości DOM elementu `value` i `field` oraz nasłuchuje CustomEvent `saddle:input`, którego `detail` jest nową wartością. Dla kolumn ustawia właściwości DOM `value` i `column` (tylko do odczytu, bez oczekiwanego zdarzenia wejściowego).

Minimalny vanilla element niestandardowy implementujący kontrakt pola:

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

Definiuj elementy na najwyższym poziomie skryptu. Przeglądarka aktualizuje wszystkie pasujące elementy, które panel już wyrenderował, gdy tylko uruchomi się `customElements.define`, więc kolejność ładowania nigdy nie ma znaczenia.

Kontrakt jest niezależny od frameworka. Działa wszystko, co kompiluje się do standardowego elementu niestandardowego: `defineCustomElement` z Vue, Lit, wrappery React lub Svelte. Autorzy pluginów nie są związani wewnętrznymi mechanizmami panelu.

## Wielodzierżawność

Saddle obsługuje opcjonalną wielodzierżawność z zakresem w URL. Włącz ją, wskazując `tenancy.model` na dowolną klasę Eloquent:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

Gdy wielodzierżawność jest aktywna, panel montuje się pod `/admin/{tenant}` zamiast `/admin`. Segment `{tenant}` jest rozwiązywany przez lookup klucza trasy na skonfigurowanym modelu. Nieznane klucze dzierżawcy zwracają **404**. Uwierzytelnieni użytkownicy, którzy nie są członkami rozwiązanego dzierżawcy, są odrzucani z **403**.

### Ograniczanie zasobów

Zadeklaruj relację BelongsTo rekordu do dzierżawcy na każdym zasobie, który chcesz ograniczyć:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

Zasoby bez `$tenant` (współdzielone tabele słownikowe, globalna konfiguracja) pozostają celowo bez zakresu.

### Gwarancje automatycznego zakresu

Każda ścieżka danych sprawdza powiązanego dzierżawcę po stronie serwera:

- **Indeks, wyszukiwanie i filtry** przechodzą przez bazowe zapytanie z zakresem (`whereBelongsTo` na zadeklarowanej relacji).
- **Lookupy rekordów** dla edycji, aktualizacji i zniszczenia są rozwiązywane przez to samo zapytanie z zakresem, więc ID z innych dzierżawców zwracają 404 przed uruchomieniem jakiejkolwiek polityki.
- **Stores** stemplują bieżącego dzierżawcę po stronie serwera po wypełnieniu formularza. Każdy klucz obcy dzierżawcy wysłany przez klienta jest nadpisywany.
- **Listy opcji relacji** stosują ten sam zakres, gdy zarejestrowany zasób modelu powiązanego również ma zakres dzierżawcy.

### Przełącznik dzierżawcy

Gdy uwierzytelniony użytkownik należy do więcej niż jednego dzierżawcy, pasek boczny panelu pokazuje select z listą wszystkich jego członkostw. Przełączenie nawiguje do tej samej ścieżki panelu pod wybranym dzierżawcą.

### Zastrzeżenia

- **Nie wystawiaj relacji `$tenant` jako pola formularza na zasobie z zakresem.** Kontroler store stempluje relację po stronie serwera, ale edytowalne pole BelongsTo wskazujące na relację dzierżawcy w formularzu aktualizacji pozwoliłoby wysłanej wartości przepiąć rekord do innego dzierżawcy.
- **Zapisana etykieta relacji nadal renderuje się w formularzu edycji, nawet gdy powiązany wiersz wypada poza bieżący zakres.** `BelongsTo` rozwiązuje bieżący wybór zapytaniem bez zakresu, aby etykieta nigdy nie znikała po zmianie zakresu. Filtrowana jest tylko lista opcji dla nowych wyborów.
- **Zmiana konfiguracji wielodzierżawności wymaga `php artisan route:clear`**, ponieważ prefiks `{tenant}` jest wybierany przy starcie. Długo działające serwery aplikacyjne (workery FPM utrzymywane między żądaniami) muszą zapewnić reset stanu żądania między żądaniami. Powiązany dzierżawca żyje na singletonie Saddle, który jest rozwiązywany świeżo per żądanie przy domyślnym czasie życia kontenera. Gdy Octane jest zainstalowany, panel automatycznie resetuje powiązanego dzierżawcę przez hooki cyklu życia żądań Octane.

## Konfiguracja

`saddle:install` publikuje `config/saddle.php`. Dostępne klucze:

| Klucz | Domyślnie | Opis |
|---|---|---|
| `path` | `'admin'` | Prefiks URL panelu (np. `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Stos middleware stosowany do wszystkich tras panelu. |
| `resources.path` | `app_path('Saddle')` | Ścieżka systemu plików skanowana w poszukiwaniu klas zasobów. |
| `resources.namespace` | `'App\\Saddle'` | Namespace PHP odpowiadający `resources.path`. |
| `per_page` | `25` | Domyślna liczba wierszy na stronę w tabelach indeksu. |
| `brand.name` | `'Saddle'` | Nazwa panelu (pasek boczny i karta przeglądarki). |
| `brand.accent` | `'#d9501f'` | Kolor akcentu (przyciski, stany aktywne). |
| `uploads.disk` | `'public'` | Domyślny dysk systemu plików używany przez pola `FileUpload`, gdy nie ustawiono per-pole `disk()`. |
| `uploads.directory` | `'saddle'` | Domyślny katalog uploadu w obrębie dysku, gdy nie ustawiono per-pole `directory()`. |

## Polecenia

| Polecenie | Opis |
|---|---|
| `saddle:install` | Publikuje konfigurację, publikuje assety panelu, tworzy `app/Saddle/`. Proponuje dodanie `saddle:upgrade` do `composer post-update-cmd`, aby assety pozostawały świeże. |
| `saddle:upgrade` | Ponownie publikuje assety panelu. Uruchom po każdej aktualizacji pakietu. |
| `saddle:resource NameResource --model=Name` | Tworzy szkielet nowej klasy zasobu. Opcja `--model` jest opcjonalna. Gdy pominięta, jest wywnioskowana z nazwy zasobu. |

**Uwaga wdrożeniowa.** Dodaj `php artisan saddle:upgrade` do skryptu wdrożeniowego po `composer install` lub `composer update`. Panel wyświetla baner ostrzegawczy w UI, gdy opublikowane assety nie są zsynchronizowane z zainstalowaną wersją pakietu.

## Rozwój lokalny

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

Katalog `workbench/` zawiera minimalną aplikację hosta używaną przez zestaw testów i do ręcznego sprawdzania. `vendor/bin/testbench serve` uruchamia ją z zarejestrowanym `HorseResource`. Pamiętaj, że trasy panelu są za middleware `auth`, a workbench dostarcza tylko stub trasy `/login`, więc do interaktywnego przeglądania tymczasowo ustaw `'middleware' => ['web']` w `config/saddle.php` albo przechodź przez testy funkcjonalne. Nie ma jeszcze seeder'a demo.

## Stack

Zbudowane dla **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## Licencja

MIT.
