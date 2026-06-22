<p align="center">
  <a href="README.ar.md">العربية</a> •
  <b>Deutsch</b> •
  <a href="../../README.md">English</a> •
  <a href="README.es.md">Español</a> •
  <a href="README.fr.md">Français</a> •
  <a href="README.it.md">Italiano</a> •
  <a href="README.ja.md">日本語</a> •
  <a href="README.ko.md">한국어</a> •
  <a href="README.nl.md">Nederlands</a> •
  <a href="README.pl.md">Polski</a> •
  <a href="README.pt-BR.md">Português (BR)</a> •
  <a href="README.ru.md">Русский</a> •
  <a href="README.tr.md">Türkçe</a> •
  <a href="README.zh-CN.md">简体中文</a>
</p>

<p align="center">
  <a href="https://saddlephp.com"><img src=".github/og.png" alt="Saddle, there's a new admin panel in town" width="820"></a>
</p>

<p align="center">
  <em>Sattel auf, Cowboy, in der Stadt gibt es ein neues Admin-Panel.</em>
</p>

---

**Saddle** ist das Open-Source-Framework für Admin-Panels für Laravel, modern gebaut für **Inertia and
Vue**. Treibe deine Eloquent-Modelle in ausgefeilte Ressourcen-Panels zusammen, mit Formular- und Tabellen-Buildern, Rollen und Zugriff,
Plugins und Mandantenfähigkeit.

> **Status: v1.0, CSV-Import/Export und zusätzliche Mandantenfunktionen.** Die Marketing-Site befindet sich unter **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## Installation

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Der Service Provider wird automatisch erkannt. `saddle:install` veröffentlicht die Konfigurationsdatei, veröffentlicht Panel-Assets und erstellt `app/Saddle/` für deine Ressourcenklassen. Besuche `/admin`, um das Panel zu sehen.

## Eine Ressource definieren

Lege Ressourcenklassen in `app/Saddle/` ab. Jede Klasse erweitert `SaddlePHP\Resource` und implementiert `form()` und `table()`.

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

Ressourcen werden beim Start automatisch durch Scannen von `app/Saddle/` erkannt, eine manuelle Registrierung ist nicht nötig.

> **Reservierte Route-Schlüssel.** Das Panel besitzt die statischen Pfadsegmente `create`, `options` und `actions` unter jeder Ressource. Ein Datensatz, dessen Route-Schlüssel wörtlich eines dieser Wörter ist, ist deshalb über seine Edit/Update/Delete-URLs nicht erreichbar. Verwende für solche Datensätze einen Integer-Schlüssel oder einen anderen Slug.

## Formularlayout

Felder können in Layout-Container gruppiert werden. Container können frei ineinander verschachtelt werden.

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

| Container | Beschreibung |
|---|---|
| `Section` | Eine beschriftete Kartengruppe. `description(string)` fügt einen Untertitel hinzu. Akzeptiert `schema([...])` mit Feldern und verschachtelten Containern. |
| `Grid` | Ordnet seine Kinder in einem CSS-Grid an. `Grid::make(2)` erstellt ein zweispaltiges Grid. Felder in einem Grid nutzen `columnSpan(int)`, um mehrere Spalten zu überspannen. |
| `Tabs` | Umschließt einen oder mehrere `Tab`-Container in einer Tab-Oberfläche. |
| `Tab` | Ein einzelner Bereich innerhalb einer `Tabs`-Gruppe. `Tab::make('Label')->schema([...])`. Wenn ein Feld in einem Tab die Validierung nicht besteht, zeigt der Tab einen Fehlerindikator, damit Benutzer das Problem finden, ohne zu jedem Bereich zu wechseln. |

**Flache Schemas funktionieren weiterhin.** Eine einfache Liste von Feldern an `$form->schema([...])` ohne Container wird vollständig unterstützt und erzeugt dasselbe Layout wie zuvor. Validierung und Autorisierung behandeln Felder identisch, egal ob sie in einem Container oder auf oberster Ebene liegen.

## Felder

| Feld | Beschreibung |
|---|---|
| `Text` | Einzeilige Texteingabe. Modifikatoren: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | Mehrzeilige Texteingabe. Modifikatoren: `rows(int)`. |
| `Select` | Dropdown mit festen Optionen. Übergebe ein assoziatives Array an `options(['value' => 'Label'])`. |
| `Toggle` | Boolescher Schalter. Speichert `true`/`false`. |
| `BelongsTo` | Beziehungsauswahl. Das Argument ist der Name der Eloquent-Beziehungsmethode auf dem Modell (`BelongsTo::make('rider')` liest `$model->rider()` und sendet den Fremdschlüssel). Optionslabels werden aus `titleAttribute('name')` aufgelöst, fallen auf die registrierte Ressource `$title` des verwandten Modells zurück und danach auf dessen Schlüssel. Wenn weder `titleAttribute()` noch eine registrierte Ressource verfügbar ist, werden Optionen mit dem Primärschlüssel beschriftet, setze also `titleAttribute('name')` für lesbare Labels. Optionen sind standardmäßig auf 100 begrenzt, überschreibe das mit `limit(int)`. `searchable()` wechselt zu einem asynchronen Picker, der die verwandte Tabelle während der Eingabe über einen authentifizierten Endpoint durchsucht. Beim Bearbeiten wird nur die aktuelle Auswahl eingebettet, die vollständige Liste wird nicht geladen. `modifyOptionsQuery(fn ($query) => ...)` begrenzt die Optionsliste für Mandantenfähigkeit oder Sichtbarkeit. Es gilt für aufgelistete und gesuchte Optionen, während die gespeicherte Auswahl eines Datensatzes ihr Label weiter rendert, auch wenn sie außerhalb des Bereichs liegt. |
| `Number` | Numerische Eingabe. Modifikatoren: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | Datumseingabe. Werte werden als `Y-m-d` gerendert. |
| `DateTime` | Eingabe für Datum und Uhrzeit (`datetime-local`). Werte werden über den Datetime-Cast deines Modells gespeichert und zurückgelesen. Ein `DateTimeInterface`-Wert wird für den Browser als `Y-m-d\TH:i` formatiert. |
| `Markdown` | Textarea mit Formatierungsleiste. Wird als einfacher String gespeichert und auf 65 535 Zeichen begrenzt, entsprechend einer MySQL-`TEXT`-Spalte. |
| `FileUpload` | Multipart-Dateiupload. Modifikatoren: `disk(string)`, `directory(string)`, `image()` (beschränkt auf Bildtypen), `acceptedTypes(array)` (MIME-Erweiterungen, z. B. `['pdf', 'docx']`), `maxSize(int $kilobytes)`. Der gespeicherte Wert ist der von `Storage::put` zurückgegebene Dateipfad. Im Bearbeitungsformular gilt: Bleibt die Eingabe unberührt, bleibt die vorhandene Datei erhalten, Leeren speichert `null`, und das Auswählen einer neuen Datei ersetzt den Pfad. Ersetzte oder geleerte Dateien werden nicht automatisch vom Datenträger gelöscht. |

## Spalten

| Spalte | Beschreibung |
|---|---|
| `TextColumn` | Rendert den rohen Attributwert. Modifikatoren: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (formatiert DateTime-Attribute, Standardformat `Y-m-d H:i`). |
| `BadgeColumn` | Rendert ein Pill-Badge. Verwende `colors(['value' => 'token'])`, um Optionswerte Farb-Tokens (`accent`, `ink`, `muted`) zuzuordnen. |
| `BooleanColumn` | Rendert ein Häkchen für truthy Werte und einen Strich für falsy Werte. |

**Beziehungsspalten und Eager Loading.** Punktnamen wie `TextColumn::make('rider.name')` lesen durch eine geladene Beziehung. Deklariere `public static array $with = ['rider']` auf der Ressource, damit die Indexabfrage die Beziehung vor dem Rendern per Eager Loading lädt. Beziehungsspalten sind noch nicht sortierbar oder durchsuchbar.

## Filter

Filter werden über `->filters([...])` auf der Tabelle deklariert. Im Index wendet das Panel sie aus `filter[name]=value`-Query-String-Parametern an. Angeforderte Werte werden gegen die Deklaration validiert, deshalb werden unbekannte Filternamen und nicht deklarierte Optionswerte still ignoriert.

| Filter | Beschreibung |
|---|---|
| `SelectFilter` | Dropdown für exakte Treffer. `options(['value' => 'Label'])` definiert sowohl die Dropdown-Auswahl als auch die Allowlist akzeptierter Werte. |
| `BooleanFilter` | Ja/Nein-Dropdown über einer booleschen Spalte. |

## Aktionen

Aktionen erscheinen als Buttons in jeder Zeile der Indextabelle. Massenaktionen erscheinen in einer Toolbar, wenn eine oder mehrere Zeilen ausgewählt sind.

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

| Fluent | Beschreibung |
|---|---|
| `label(string)` | Anzeigelabel auf dem Button. Wenn es fehlt, wird der Name in Title Case umgewandelt. |
| `color(string)` | Farb-Token für den Button: `accent`, `ink` oder `muted`. Standard ist `ink`. |
| `requiresConfirmation(?string)` | Zeigt vor der Ausführung einen Bestätigungsdialog. Übergib eine eigene Nachricht oder lasse sie weg, um die Standardabfrage zu verwenden. |
| `authorize(string)` | Benennt eine Richtlinienfähigkeit, die pro Datensatz geprüft wird, bevor der Handler läuft. |
| `successMessage(string)` | Flash-Nachricht nach einer erfolgreichen Ausführung. Standard ist `Done.`. |

Aktionen posten an einen geschützten Endpoint. Datensätze werden über dieselbe bereichsbezogene Basisabfrage aufgelöst, die überall sonst verwendet wird, sodass Mandantenfähigkeit, Filter und ressourcenspezifische Query-Scopes automatisch gelten. Wenn `authorize('ability')` deklariert ist, wird die Richtlinie pro Datensatz geprüft, bevor der Handler läuft. Massenläufe werden in einer Datenbanktransaktion ausgeführt und sind auf 100 Datensätze pro Anfrage begrenzt. Fehlt ein angeforderter Datensatz im bereichsbezogenen Abruf, bricht die gesamte Operation mit 404 ab, statt sie still auf die aufgelöste Teilmenge anzuwenden. Deklariere `authorize()` für jede destruktive Aktion.

`BulkAction::delete()` ist ein vorgefertigtes Preset: Name `delete`, Label `Delete`, Farbe `accent`, Bestätigung `Delete the selected records?`, und `authorize('delete')` ist bereits verdrahtet.

## Autorisierung

Saddle nutzt standardmäßige Laravel-Richtlinien. Registriere eine Richtlinie für ein Modell, und das Panel erzwingt sie überall: Index, Formulare, Zeilenaktionen und Beziehungsauswahlen. Wenn keine Richtlinie registriert ist, sind alle Fähigkeiten für jeden authentifizierten Benutzer erlaubt. Rollen bleiben in deiner Anwendung: Jedes Rollenpaket oder jede selbst gebaute Schicht, die deine Richtlinien stützt, funktioniert unverändert.

### Das Panel absichern

Ressourcen ohne registrierte Richtlinie erlauben standardmäßig jeden authentifizierten Benutzer. Setze `saddle.authorization.require_policy` auf `true`, um geschlossen zu scheitern: Ressourcen ohne Richtlinie werden unzugänglich statt offen. Du kannst auch eine Gate-Middleware zu `saddle.middleware` hinzufügen, um vor jeder Panel-Route eine pauschale Prüfung auszuführen. Wenn dein Web-Guard von Endbenutzern und Administratoren gemeinsam genutzt wird, ist eine dieser Kontrollen unverzichtbar.

| Fähigkeit | Wo sie geprüft wird |
|---|---|
| `viewAny` | Ressourcen-Indexseite, Sichtbarkeit in der Seitenleiste |
| `create` | Erstellformular, Store-Aktion, Endpoint für Beziehungsoptionen |
| `update` | Bearbeitungsformular, Update-Aktion, Edit-Link pro Zeile, Endpoint für Beziehungsoptionen (gegen ein frisches Modell geprüft, wenn kein Datensatz im Bereich liegt) |
| `delete` | Destroy-Aktion, Delete-Button pro Zeile |

### Feldsichtbarkeit mit `canSee`

Einzelne Felder können pro Anfrage mit `canSee` geschützt werden. Das Feld `notes` in `HorseResource` ist ein funktionierendes Beispiel:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

Ausgeblendete Felder werden aus der Formular-Payload entfernt (gespeicherte Werte werden nie an das Frontend serialisiert), tragen keine Validierungsregeln bei, werden beim Speichern nie geschrieben, und ihr Endpoint für Beziehungsoptionen gibt 404 zurück. Der Callback kann mehrmals pro Anfrage laufen, halte ihn also günstig und gib einen echten Boolean zurück. Verwende zum Beispiel `Gate::allows('view-notes', $model)` statt `Gate::inspect(...)`, dessen `Response`-Objekt immer truthy ist und das Feld nie ausblenden wird.

## Plugins

Ein Plugin ist ein reguläres Composer-Paket. Sein Service Provider registriert Ressourcen, Skripte und Styles über die `Saddle`-Fassade, und Laravels Paket-Autoerkennung startet es automatisch zusammen mit deiner Anwendung.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

Veröffentliche kompilierte Assets aus dem Service Provider des Plugins nach `public/vendor/{plugin}` mit dem standardmäßigen `$this->publishes([...])`-Mechanismus und zeige dann mit `Saddle::script()` auf den veröffentlichten Pfad. Plugin-Skripte und Stylesheets werden auf jeder Panel-Seite nach dem Kernbundle des Panels geladen.

### Custom Elements

Plugins können eigene Renderer für Felder und Spalten als Custom Elements ausliefern. Auf der PHP-Seite:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

Das Panel erfüllt diesen Vertrag: Für Felder setzt es die DOM-Eigenschaften `value` und `field` des Elements und lauscht auf ein `saddle:input` CustomEvent, dessen `detail` der neue Wert ist. Für Spalten setzt es die DOM-Eigenschaften `value` und `column` (schreibgeschützt, kein Eingabe-Event erwartet).

Ein minimales Vanilla-Custom-Element, das den Feldvertrag implementiert:

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

Definiere Elemente auf oberster Ebene deines Skripts. Der Browser aktualisiert alle passenden Elemente, die das Panel bereits gerendert hat, sobald `customElements.define` läuft, daher spielt die Ladereihenfolge keine Rolle.

Der Vertrag ist framework-agnostisch. Alles, was zu einem Standard-Custom-Element kompiliert, funktioniert: Vues `defineCustomElement`, Lit, React- oder Svelte-Wrapper. Plugin-Autoren sind nicht an die Interna des Panels gebunden.

## Mandantenfähigkeit

Saddle unterstützt optionale, URL-bezogene Mandantenfähigkeit. Aktiviere sie, indem du `tenancy.model` auf eine beliebige Eloquent-Klasse zeigst:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

Wenn Mandantenfähigkeit aktiv ist, wird das Panel unter `/admin/{tenant}` statt `/admin` eingebunden. Das Segment `{tenant}` wird per Route-Key-Lookup auf dem konfigurierten Modell aufgelöst. Unbekannte Mandantenschlüssel geben **404** zurück. Authentifizierte Benutzer, die kein Mitglied des aufgelösten Mandanten sind, werden mit **403** abgewiesen.

### Ressourcen eingrenzen

Deklariere auf jeder Ressource, die du eingrenzen möchtest, die BelongsTo-Beziehung des Datensatzes zum Mandanten:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

Ressourcen ohne `$tenant` (gemeinsame Lookup-Tabellen, globale Konfiguration) bleiben absichtlich uneingeschränkt.

### Garantien für automatische Eingrenzung

Jeder Datenpfad prüft den gebundenen Mandanten serverseitig:

- **Index, Suche und Filter** laufen durch die bereichsbezogene Basisabfrage (`whereBelongsTo` auf der deklarierten Beziehung).
- **Datensatz-Lookups** für Bearbeiten, Aktualisieren und Löschen werden über dieselbe bereichsbezogene Abfrage aufgelöst, sodass mandantenübergreifende IDs 404 zurückgeben, bevor eine Richtlinie läuft.
- **Stores** stempeln den aktuellen Mandanten serverseitig nach dem Befüllen des Formulars. Jeder vom Client gesendete Mandanten-Fremdschlüssel wird überschrieben.
- **Listen für Beziehungsoptionen** wenden denselben Bereich an, wenn die registrierte Ressource des verwandten Modells ebenfalls mandantenbezogen ist.

### Mandantenwechsler

Wenn der authentifizierte Benutzer zu mehr als einem Mandanten gehört, zeigt die Seitenleiste des Panels ein Select mit allen Mitgliedschaften. Ein Wechsel navigiert zum selben Panel-Pfad unter dem ausgewählten Mandanten.

### Hinweise

- **Stelle die `$tenant`-Beziehung auf einer bereichsbezogenen Ressource nicht als Formularfeld bereit.** Der Store-Controller stempelt die Beziehung serverseitig, aber ein bearbeitbares BelongsTo-Feld, das auf die Mandantenbeziehung in einem Update-Formular zeigt, würde es einem gesendeten Wert erlauben, den Datensatz auf einen anderen Mandanten umzuhängen.
- **Ein gespeichertes Beziehungslabel wird im Bearbeitungsformular weiterhin gerendert, auch wenn die verwandte Zeile außerhalb des aktuellen Bereichs liegt.** `BelongsTo` löst die aktuelle Auswahl mit einer uneingeschränkten Abfrage auf, damit das Label nach einer Bereichsänderung nie verschwindet. Nur die Optionsliste für neue Auswahlen wird gefiltert.
- **Eine Änderung der Mandantenkonfiguration erfordert `php artisan route:clear`**, weil das Präfix `{tenant}` beim Start entschieden wird. Langlebige Anwendungsserver (FPM-Worker, die über Anfragen hinweg am Leben bleiben) müssen sicherstellen, dass der Anfragezustand zwischen Anfragen zurückgesetzt wird. Der gebundene Mandant lebt auf dem Saddle-Singleton, das unter der Standard-Container-Lebensdauer pro Anfrage frisch aufgelöst wird. Wenn Octane installiert ist, setzt das Panel den gebundenen Mandanten automatisch über die Octane-Request-Lifecycle-Hooks zurück.

## Konfiguration

`saddle:install` veröffentlicht `config/saddle.php`. Verfügbare Schlüssel:

| Schlüssel | Standard | Beschreibung |
|---|---|---|
| `path` | `'admin'` | URL-Präfix für das Panel (z. B. `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Middleware-Stack, der auf alle Panel-Routen angewendet wird. |
| `resources.path` | `app_path('Saddle')` | Dateisystempfad, der nach Ressourcenklassen gescannt wird. |
| `resources.namespace` | `'App\\Saddle'` | PHP-Namespace, der `resources.path` entspricht. |
| `per_page` | `25` | Standardzeilen pro Seite in Indextabellen. |
| `brand.name` | `'Saddle'` | Panelname (Seitenleiste und Browser-Tab). |
| `brand.accent` | `'#d9501f'` | Akzentfarbe (Buttons, aktive Zustände). |
| `uploads.disk` | `'public'` | Standard-Dateisystem-Disk, die von `FileUpload`-Feldern verwendet wird, wenn kein feldbezogenes `disk()` gesetzt ist. |
| `uploads.directory` | `'saddle'` | Standard-Upload-Verzeichnis innerhalb der Disk, wenn kein feldbezogenes `directory()` gesetzt ist. |

## Befehle

| Befehl | Beschreibung |
|---|---|
| `saddle:install` | Konfiguration veröffentlichen, Panel-Assets veröffentlichen, `app/Saddle/` erstellen. Bietet an, `saddle:upgrade` zu `composer post-update-cmd` hinzuzufügen, damit Assets frisch bleiben. |
| `saddle:upgrade` | Panel-Assets erneut veröffentlichen. Nach jedem Paketupdate ausführen. |
| `saddle:resource NameResource --model=Name` | Gerüst für eine neue Ressourcenklasse erzeugen. Die Option `--model` ist optional. Wenn sie fehlt, wird sie aus dem Ressourcennamen abgeleitet. |

**Deploy-Hinweis.** Füge `php artisan saddle:upgrade` nach `composer install` oder `composer update` zu deinem Deploy-Skript hinzu. Das Panel zeigt in der UI ein Warnbanner, wenn die veröffentlichten Assets nicht zur installierten Paketversion passen.

## Lokale Entwicklung

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

Das Verzeichnis `workbench/` enthält eine minimale Host-Anwendung, die von der Testsuite und für manuelle Tests verwendet wird. `vendor/bin/testbench serve` startet sie mit registriertem `HorseResource`. Beachte, dass Panel-Routen hinter der `auth`-Middleware liegen und die Workbench nur eine Stub-Route `/login` mitliefert. Für interaktives Browsen setze daher entweder vorübergehend `'middleware' => ['web']` in `config/saddle.php` oder gehe stattdessen über die Feature-Tests. Es gibt noch keinen Demo-Seeder.

## Stack

Gebaut für **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## Lizenz

MIT.
