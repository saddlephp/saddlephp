<p align="center">
  <a href="README.ar.md">العربية</a> •
  <a href="README.de.md">Deutsch</a> •
  <a href="../../README.md">English</a> •
  <a href="README.es.md">Español</a> •
  <a href="README.fr.md">Français</a> •
  <a href="README.it.md">Italiano</a> •
  <a href="README.ja.md">日本語</a> •
  <a href="README.ko.md">한국어</a> •
  <b>Nederlands</b> •
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
  <em>Zadel op, cowboy, er is een nieuw beheerpaneel in de stad.</em>
</p>

---

**Saddle** is het open-source framework voor beheerpanelen voor Laravel, modern gebouwd voor **Inertia and
Vue**. Breng je Eloquent-modellen samen in verzorgde resourcepanelen, met builders voor formulieren en tabellen, rollen en toegang,
plugins en multi-tenant ondersteuning.

> **Status: v1.0, CSV-import/export en extra's voor tenants.** De marketingsite staat op **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## Installatie

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

De serviceprovider wordt automatisch ontdekt. `saddle:install` publiceert het configuratiebestand, publiceert paneelassets en maakt `app/Saddle/` aan voor je resourceklassen. Bezoek `/admin` om het paneel te zien.

## Een resource definiëren

Plaats resourceklassen in `app/Saddle/`. Elke klasse breidt `SaddlePHP\Resource` uit en implementeert `form()` en `table()`.

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

Resources worden automatisch ontdekt door `app/Saddle/` bij het opstarten te scannen, handmatige registratie is niet nodig.

> **Gereserveerde routesleutels.** Het paneel bezit de statische padsegmenten `create`, `options` en `actions` onder elke resource, dus een record waarvan de routesleutel letterlijk een van die woorden is, is niet bereikbaar via de URL's voor bewerken/bijwerken/verwijderen. Gebruik voor zulke records een integer-sleutel of een andere slug.

## Formulierlayout

Velden kunnen worden gegroepeerd in layoutcontainers. Containers kunnen vrij in elkaar worden genest.

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

| Container | Beschrijving |
|---|---|
| `Section` | Een gelabelde kaartgroep. `description(string)` voegt een ondertitel toe. Accepteert `schema([...])` met velden en geneste containers. |
| `Grid` | Rangschikt zijn kinderen in een CSS-grid. `Grid::make(2)` maakt een grid met twee kolommen. Velden binnen een Grid gebruiken `columnSpan(int)` om meerdere kolommen te overspannen. |
| `Tabs` | Wikkelt een of meer `Tab`-containers in een interface met tabbladen. |
| `Tab` | Een enkel paneel binnen een `Tabs`-groep. `Tab::make('Label')->schema([...])`. Wanneer een veld in een tabblad niet valideert, toont het tabblad een foutindicator zodat gebruikers het probleem kunnen vinden zonder naar elk paneel te wisselen. |

**Platte schema's werken nog steeds.** Een gewone lijst met velden doorgeven aan `$form->schema([...])` zonder containers wordt volledig ondersteund en levert dezelfde layout op als voorheen. Validatie en autorisatie behandelen velden identiek, ongeacht of ze in een container of op het hoogste niveau staan.

## Velden

| Veld | Beschrijving |
|---|---|
| `Text` | Tekstinvoer op één regel. Modifiers: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | Tekstinvoer met meerdere regels. Modifiers: `rows(int)`. |
| `Select` | Dropdown met vaste opties. Geef een associatieve array door aan `options(['value' => 'Label'])`. |
| `Toggle` | Booleaanse schakelaar. Slaat `true`/`false` op. |
| `BelongsTo` | Relatieselectie. Het argument is de naam van de Eloquent-relatiemethode op het model (`BelongsTo::make('rider')` leest `$model->rider()` en verstuurt de foreign key). Optielabels worden opgelost vanuit `titleAttribute('name')`, met fallback naar de geregistreerde resource `$title` van het gerelateerde model en daarna naar de sleutel. Als er geen `titleAttribute()` en geen geregistreerde resource beschikbaar is, worden opties gelabeld met de primaire sleutel, dus stel `titleAttribute('name')` in voor leesbare labels. Opties zijn standaard beperkt tot 100; overschrijf dit met `limit(int)`. `searchable()` schakelt over naar een asynchrone picker die de gerelateerde tabel doorzoekt terwijl je typt via een geauthenticeerd endpoint. Bij bewerken wordt alleen de huidige selectie ingebed, de volledige lijst wordt niet geladen. `modifyOptionsQuery(fn ($query) => ...)` beperkt de optielijst voor tenants of zichtbaarheid. Dit geldt voor getoonde en gezochte opties, terwijl de opgeslagen selectie van een record zijn label blijft renderen, ook wanneer die buiten de scope valt. |
| `Number` | Numerieke invoer. Modifiers: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | Datuminvoer. Waarden worden gerenderd als `Y-m-d`. |
| `DateTime` | Datum-en-tijdinvoer (`datetime-local`). Waarden worden opgeslagen en teruggelezen via de datetime-cast van je model. Een `DateTimeInterface`-waarde wordt voor de browser geformatteerd als `Y-m-d\TH:i`. |
| `Markdown` | Textarea met een opmaaktoolbar. Wordt opgeslagen als platte string en begrensd op 65 535 tekens, gelijk aan een MySQL `TEXT`-kolom. |
| `FileUpload` | Multipart bestandsupload. Modifiers: `disk(string)`, `directory(string)`, `image()` (beperkt tot afbeeldingstypen), `acceptedTypes(array)` (MIME-extensies, bijv. `['pdf', 'docx']`), `maxSize(int $kilobytes)`. De opgeslagen waarde is het bestandspad dat door `Storage::put` wordt teruggegeven. Op het bewerkingsformulier: de invoer ongemoeid laten behoudt het bestaande bestand, leegmaken slaat `null` op, en een nieuw bestand kiezen vervangt het pad. Vervangen of gewiste bestanden worden niet automatisch van schijf verwijderd. |

## Kolommen

| Kolom | Beschrijving |
|---|---|
| `TextColumn` | Rendert de ruwe attribuutwaarde. Modifiers: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (formatteert DateTime-attributen, standaardformaat `Y-m-d H:i`). |
| `BadgeColumn` | Rendert een pill-badge. Gebruik `colors(['value' => 'token'])` om optiewaarden aan kleurtokens (`accent`, `ink`, `muted`) te koppelen. |
| `BooleanColumn` | Rendert een vinkje voor truthy waarden en een streepje voor falsy waarden. |

**Relatiekolommen en eager loading.** Namen met punten zoals `TextColumn::make('rider.name')` lezen via een geladen relatie. Declareer `public static array $with = ['rider']` op de resource zodat de indexquery de relatie eager-loadt voordat er wordt gerenderd. Relatiekolommen zijn nog niet sorteerbaar of doorzoekbaar.

## Filters

Filters worden op de tabel gedeclareerd via `->filters([...])`. Op de index past het paneel ze toe vanuit query string-parameters `filter[name]=value`. Aangevraagde waarden worden tegen de declaratie gevalideerd, dus onbekende filternamen en niet-gedeclareerde optiewaarden worden stil genegeerd.

| Filter | Beschrijving |
|---|---|
| `SelectFilter` | Dropdown voor exacte match. `options(['value' => 'Label'])` definieert zowel de dropdownkeuzes als de allowlist van geaccepteerde waarden. |
| `BooleanFilter` | Ja/Nee-dropdown over een booleaanse kolom. |

## Acties

Acties verschijnen als knoppen op elke rij van de indextabel. Bulkacties verschijnen in een toolbar wanneer een of meer rijen zijn geselecteerd.

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

| Fluent | Beschrijving |
|---|---|
| `label(string)` | Weergavelabel op de knop. Als het ontbreekt, wordt de naam omgezet naar title case. |
| `color(string)` | Kleurtoken voor de knop: `accent`, `ink` of `muted`. Standaard `ink`. |
| `requiresConfirmation(?string)` | Toon een bevestigingsdialoog vóór uitvoering. Geef een eigen bericht door of laat weg om de standaardprompt te gebruiken. |
| `authorize(string)` | Noemt een beleidscapability die per record wordt gecontroleerd voordat de handler draait. |
| `successMessage(string)` | Flashbericht dat na een succesvolle run wordt getoond. Standaard `Done.`. |

Acties posten naar een bewaakt endpoint. Records worden opgelost via dezelfde gescopete basisquery die overal wordt gebruikt, zodat tenants, filters en queryscopes per resource automatisch gelden. Wanneer `authorize('ability')` is gedeclareerd, wordt het beleid per record gecontroleerd voordat de handler draait. Bulkruns worden uitgevoerd binnen een databasetransactie en zijn beperkt tot 100 records per request. Als een aangevraagd record ontbreekt in de gescopete fetch, breekt de hele operatie af met 404 in plaats van stil op de opgeloste subset te worden toegepast. Declareer `authorize()` op elke destructieve actie.

`BulkAction::delete()` is een kant-en-klare preset: naam `delete`, label `Delete`, kleur `accent`, bevestiging `Delete the selected records?`, en `authorize('delete')` is al aangesloten.

## Autorisatie

Saddle gebruikt standaard Laravel-beleid. Registreer een beleid voor een model en het paneel dwingt het overal af: index, formulieren, rijacties en relatiepickers. Zonder geregistreerd beleid zijn alle capabilities toegestaan voor elke geauthenticeerde gebruiker. Rollen blijven in je applicatie: elk rollenpakket of zelfgebouwde laag die je beleid ondersteunt werkt ongewijzigd.

### Het paneel afsluiten

Resources zonder geregistreerd beleid laten standaard elke geauthenticeerde gebruiker toe. Zet `saddle.authorization.require_policy` op `true` om gesloten te falen: resources zonder beleid worden ontoegankelijk in plaats van open. Je kunt ook een gate-middleware toevoegen aan `saddle.middleware` voor een algemene controle voordat een paneelroute draait. Als je web guard wordt gedeeld tussen eindgebruikers en beheerders, is een van deze controles essentieel.

| Capability | Waar die wordt gecontroleerd |
|---|---|
| `viewAny` | Resource-indexpagina, zichtbaarheid in de zijbalk |
| `create` | Aanmaakformulier, store-actie, endpoint voor relatieopties |
| `update` | Bewerkingsformulier, update-actie, Edit-link per rij, endpoint voor relatieopties (gecontroleerd tegen een vers model wanneer geen record in scope is) |
| `delete` | Destroy-actie, Delete-knop per rij |

### Veldzichtbaarheid met `canSee`

Individuele velden kunnen per request worden afgeschermd met `canSee`. Het veld `notes` in `HorseResource` is een werkend voorbeeld:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

Verborgen velden worden uit de formulierpayload gestript (opgeslagen waarden worden nooit naar de frontend geserialiseerd), dragen geen validatieregels bij, worden nooit bij opslaan geschreven, en hun endpoint voor relatieopties geeft 404 terug. De callback kan meerdere keren per request draaien, houd hem dus goedkoop en geef een echte boolean terug. Gebruik bijvoorbeeld `Gate::allows('view-notes', $model)` in plaats van `Gate::inspect(...)`, waarvan het `Response`-object altijd truthy is en het veld nooit zal verbergen.

## Plugins

Een plugin is een regulier Composer-pakket. De serviceprovider registreert resources, scripts en styles via de `Saddle`-facade, en Laravel's package auto-discovery start het automatisch mee met je applicatie.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

Publiceer gecompileerde assets vanuit de serviceprovider van de plugin naar `public/vendor/{plugin}` met het standaard `$this->publishes([...])`-mechanisme en wijs daarna `Saddle::script()` naar het gepubliceerde pad. Pluginscripts en stylesheets worden op elke paneelpagina geladen na de core bundle van het paneel.

### Aangepaste elementen

Plugins kunnen hun eigen veld- en kolomrenderers als aangepaste elementen leveren. Aan de PHP-kant:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

Het paneel voldoet aan dit contract: voor velden zet het de DOM-eigenschappen `value` en `field` van het element en luistert het naar een `saddle:input` CustomEvent waarvan `detail` de nieuwe waarde is. Voor kolommen zet het `value` en `column` DOM-eigenschappen (alleen-lezen, geen invoerevent verwacht).

Een minimaal vanilla aangepast element dat het veldcontract implementeert:

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

Definieer elementen op het hoogste niveau van je script. De browser upgradet alle overeenkomende elementen die het paneel al heeft gerenderd zodra `customElements.define` draait, dus de laadvolgorde doet er nooit toe.

Het contract is framework-agnostisch. Alles wat compileert naar een standaard aangepast element werkt: Vue's `defineCustomElement`, Lit, React- of Svelte-wrappers. Plugin-auteurs zitten niet vast aan de interne werking van het paneel.

## Multi-tenant

Saddle ondersteunt optionele, URL-gescopete multi-tenant werking. Schakel dit in door `tenancy.model` naar een willekeurige Eloquent-klasse te laten wijzen:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

Wanneer tenants actief zijn, wordt het paneel gemount onder `/admin/{tenant}` in plaats van `/admin`. Het segment `{tenant}` wordt opgelost via routesleutel-lookup op het geconfigureerde model. Onbekende tenantsleutels geven **404** terug. Geauthenticeerde gebruikers die geen lid zijn van de opgeloste tenant worden afgewezen met **403**.

### Resources scopen

Declareer de BelongsTo-relatie van het record naar de tenant op elke resource die je wilt scopen:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

Resources zonder `$tenant` (gedeelde lookuptabellen, globale configuratie) blijven bewust ongescoped.

### Automatische scopegaranties

Elk datapad controleert de gebonden tenant server-side:

- **Index, zoeken en filters** lopen via de gescopete basisquery (`whereBelongsTo` op de gedeclareerde relatie).
- **Recordlookups** voor bewerken, bijwerken en vernietigen worden opgelost via dezelfde gescopete query, zodat cross-tenant IDs 404 teruggeven voordat er beleid draait.
- **Stores** stempelen de huidige tenant server-side na het vullen van het formulier. Elke tenant-foreign key die door de client wordt ingediend, wordt overschreven.
- **Relatieoptielijsten** passen dezelfde scope toe wanneer de geregistreerde resource van het gerelateerde model ook tenant-gescopet is.

### Tenantwisselaar

Wanneer de geauthenticeerde gebruiker bij meer dan één tenant hoort, toont de zijbalk van het paneel een select met al hun lidmaatschappen. Wisselen navigeert naar hetzelfde paneelpad onder de geselecteerde tenant.

### Kanttekeningen

- **Stel de `$tenant`-relatie op een gescopete resource niet bloot als formulierveld.** De store-controller stempelt de relatie server-side, maar een bewerkbaar BelongsTo-veld dat naar de tenantrelatie op een updateformulier wijst, zou een ingestuurde waarde het record naar een andere tenant laten verplaatsen.
- **Een opgeslagen relatielabel rendert nog steeds op het bewerkingsformulier, ook wanneer de gerelateerde rij buiten de huidige scope valt.** `BelongsTo` lost de huidige selectie op met een ongescopete query zodat het label nooit verdwijnt na een scopewijziging. Alleen de optielijst voor nieuwe selecties wordt gefilterd.
- **Het wijzigen van de tenantconfiguratie vereist `php artisan route:clear`**, omdat het `{tenant}`-prefix bij het opstarten wordt besloten. Langlopende applicatieservers (FPM-workers die tussen requests in leven blijven) moeten ervoor zorgen dat requeststatus tussen requests wordt gereset. De gebonden tenant leeft op de Saddle-singleton, die per request vers wordt opgelost onder de standaard levensduur van de container. Wanneer Octane is geïnstalleerd, reset het paneel de gebonden tenant automatisch via de request-lifecycle hooks van Octane.

## Configuratie

`saddle:install` publiceert `config/saddle.php`. Beschikbare sleutels:

| Sleutel | Standaard | Beschrijving |
|---|---|---|
| `path` | `'admin'` | URL-prefix voor het paneel (bijv. `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Middleware-stack toegepast op alle paneelroutes. |
| `resources.path` | `app_path('Saddle')` | Bestandssysteempad dat wordt gescand voor resourceklassen. |
| `resources.namespace` | `'App\\Saddle'` | PHP-namespace die overeenkomt met `resources.path`. |
| `per_page` | `25` | Standaard aantal rijen per pagina op indextabellen. |
| `brand.name` | `'Saddle'` | Paneelnaam (zijbalk en browsertab). |
| `brand.accent` | `'#d9501f'` | Accentkleur (knoppen, actieve states). |
| `uploads.disk` | `'public'` | Standaard bestandssysteemdisk die door `FileUpload`-velden wordt gebruikt wanneer geen veldspecifieke `disk()` is ingesteld. |
| `uploads.directory` | `'saddle'` | Standaard uploaddirectory binnen de disk wanneer geen veldspecifieke `directory()` is ingesteld. |

## Commando's

| Commando | Beschrijving |
|---|---|
| `saddle:install` | Publiceert configuratie, publiceert paneelassets, maakt `app/Saddle/` aan. Biedt aan `saddle:upgrade` toe te voegen aan `composer post-update-cmd` zodat assets vers blijven. |
| `saddle:upgrade` | Publiceert paneelassets opnieuw. Draai na elke pakketupdate. |
| `saddle:resource NameResource --model=Name` | Scaffoldt een nieuwe resourceklasse. De optie `--model` is optioneel. Deze wordt afgeleid uit de resourcenaam wanneer hij ontbreekt. |

**Deploynotitie.** Voeg `php artisan saddle:upgrade` toe aan je deployscript na `composer install` of `composer update`. Het paneel toont een waarschuwingsbanner in de UI wanneer de gepubliceerde assets niet synchroon zijn met de geïnstalleerde pakketversie.

## Lokale ontwikkeling

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

De map `workbench/` bevat een minimale hostapplicatie die wordt gebruikt door de testsuite en voor handmatig proberen. `vendor/bin/testbench serve` start deze met geregistreerde `HorseResource`. Let erop dat paneelroutes achter de `auth`-middleware zitten en dat de workbench alleen een stubroute `/login` meelevert. Voor interactief browsen zet je dus tijdelijk `'middleware' => ['web']` in `config/saddle.php` of blader je via de featuretests. Er is nog geen demo-seeder.

## Stack

Gebouwd voor **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## Licentie

MIT.
