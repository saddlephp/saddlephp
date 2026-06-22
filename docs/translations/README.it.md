<p align="center">
  <a href="README.ar.md">العربية</a> •
  <a href="README.de.md">Deutsch</a> •
  <a href="../../README.md">English</a> •
  <a href="README.es.md">Español</a> •
  <a href="README.fr.md">Français</a> •
  <b>Italiano</b> •
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
  <em>In sella, cowboy, c'è un nuovo pannello di amministrazione in città.</em>
</p>

---

**Saddle** è il framework open-source per pannelli di amministrazione per Laravel, costruito in modo moderno per **Inertia and
Vue**. Raduna i tuoi modelli Eloquent in pannelli di risorse rifiniti, con builder per form e tabelle, ruoli e accesso,
plugin e multi-tenancy.

> **Stato: v1.0, import/export CSV ed extra per la multi-tenancy.** Il sito marketing si trova su **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## Installazione

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Il service provider viene rilevato automaticamente. `saddle:install` pubblica il file di configurazione, pubblica gli asset del pannello e crea `app/Saddle/` per le tue classi di risorse. Visita `/admin` per vedere il pannello.

## Definire una risorsa

Metti le classi di risorse in `app/Saddle/`. Ogni classe estende `SaddlePHP\Resource` e implementa `form()` e `table()`.

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

Le risorse vengono scoperte automaticamente scansionando `app/Saddle/` all'avvio, senza bisogno di registrazione manuale.

> **Chiavi di rotta riservate.** Il pannello possiede i segmenti di percorso statici `create`, `options` e `actions` sotto ogni risorsa, quindi un record la cui chiave di rotta è letteralmente una di quelle parole non è raggiungibile dalle sue URL di modifica/aggiornamento/eliminazione. Usa una chiave intera o uno slug diverso per quei record.

## Layout del form

I campi possono essere raggruppati in contenitori di layout. I contenitori si possono annidare liberamente l'uno dentro l'altro.

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

| Contenitore | Descrizione |
|---|---|
| `Section` | Un gruppo di card con etichetta. `description(string)` aggiunge un sottotitolo. Accetta `schema([...])` di campi e contenitori annidati. |
| `Grid` | Dispone i figli in una griglia CSS. `Grid::make(2)` crea una griglia a due colonne. I campi dentro un Grid usano `columnSpan(int)` per occupare più colonne. |
| `Tabs` | Racchiude uno o più contenitori `Tab` in un'interfaccia a schede. |
| `Tab` | Un singolo pannello dentro un gruppo `Tabs`. `Tab::make('Label')->schema([...])`. Quando un campo dentro una scheda non supera la validazione, la scheda mostra un indicatore di errore così gli utenti possono trovare il problema senza passare su ogni pannello. |

**Gli schemi piatti continuano a funzionare.** Passare una semplice lista di campi a `$form->schema([...])` senza contenitori è pienamente supportato e produce lo stesso layout di prima. Validazione e autorizzazione trattano i campi allo stesso modo, indipendentemente dal fatto che vivano dentro un contenitore o al livello superiore.

## Campi

| Campo | Descrizione |
|---|---|
| `Text` | Input di testo su una riga. Modificatori: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | Input di testo multilinea. Modificatori: `rows(int)`. |
| `Select` | Dropdown con opzioni fisse. Passa un array associativo a `options(['value' => 'Label'])`. |
| `Toggle` | Interruttore booleano. Salva `true`/`false`. |
| `BelongsTo` | Selettore di relazione. L'argomento è il nome del metodo di relazione Eloquent sul modello (`BelongsTo::make('rider')` legge `$model->rider()` e invia la chiave esterna). Le etichette delle opzioni si risolvono da `titleAttribute('name')`, ripiegando sulla risorsa registrata `$title` del modello correlato e poi sulla sua chiave. Se non sono disponibili né `titleAttribute()` né una risorsa registrata, le opzioni sono etichettate con la chiave primaria, quindi imposta `titleAttribute('name')` per etichette leggibili. Le opzioni sono limitate a 100 per impostazione predefinita, sovrascrivi con `limit(int)`. `searchable()` passa a un selettore asincrono che cerca nella tabella correlata mentre digiti tramite un endpoint autenticato. In modifica, viene incorporata solo la selezione corrente, la lista completa non viene caricata. `modifyOptionsQuery(fn ($query) => ...)` limita la lista delle opzioni per multi-tenancy o visibilità. Si applica alle opzioni elencate e cercate, mentre la selezione salvata di un record continua a renderizzare la sua etichetta anche quando è fuori dall'ambito. |
| `Number` | Input numerico. Modificatori: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | Input data. I valori vengono renderizzati come `Y-m-d`. |
| `DateTime` | Input data e ora (`datetime-local`). I valori sono salvati e riletti tramite il cast datetime del tuo modello. Un valore `DateTimeInterface` è formattato come `Y-m-d\TH:i` per il browser. |
| `Markdown` | Textarea con barra di formattazione. Salvato come stringa semplice e limitato a 65 535 caratteri, equivalente a una colonna MySQL `TEXT`. |
| `FileUpload` | Upload file multipart. Modificatori: `disk(string)`, `directory(string)`, `image()` (limita ai tipi immagine), `acceptedTypes(array)` (estensioni MIME, per es. `['pdf', 'docx']`), `maxSize(int $kilobytes)`. Il valore salvato è il percorso del file restituito da `Storage::put`. Nel form di modifica: lasciare intatto l'input mantiene il file esistente, svuotarlo salva `null` e scegliere un nuovo file sostituisce il percorso. I file sostituiti o svuotati non vengono eliminati automaticamente dal disco. |

## Colonne

| Colonna | Descrizione |
|---|---|
| `TextColumn` | Renderizza il valore grezzo dell'attributo. Modificatori: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (formatta attributi DateTime, formato predefinito `Y-m-d H:i`). |
| `BadgeColumn` | Renderizza un badge a pillola. Usa `colors(['value' => 'token'])` per mappare i valori delle opzioni ai token colore (`accent`, `ink`, `muted`). |
| `BooleanColumn` | Renderizza un segno di spunta per valori truthy e un trattino per valori falsy. |

**Colonne di relazione ed eager loading.** Nomi puntati come `TextColumn::make('rider.name')` leggono attraverso una relazione caricata. Dichiara `public static array $with = ['rider']` sulla risorsa così la query di indice carica la relazione prima del rendering. Le colonne di relazione non sono ancora ordinabili o ricercabili.

## Filtri

I filtri sono dichiarati sulla tabella tramite `->filters([...])`. Nell'indice, il pannello li applica dai parametri di query string `filter[name]=value`. I valori richiesti sono validati rispetto alla dichiarazione, quindi nomi di filtro sconosciuti e valori di opzione non dichiarati vengono ignorati silenziosamente.

| Filtro | Descrizione |
|---|---|
| `SelectFilter` | Dropdown a corrispondenza esatta. `options(['value' => 'Label'])` definisce sia le scelte del dropdown sia la allowlist dei valori accettati. |
| `BooleanFilter` | Dropdown Sì/No su una colonna booleana. |

## Azioni

Le azioni appaiono come pulsanti su ogni riga della tabella indice. Le azioni massive appaiono in una toolbar quando una o più righe sono selezionate.

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

| Fluent | Descrizione |
|---|---|
| `label(string)` | Etichetta visualizzata sul pulsante. Se omessa, il nome viene convertito in title case. |
| `color(string)` | Token colore per il pulsante: `accent`, `ink` o `muted`. Predefinito `ink`. |
| `requiresConfirmation(?string)` | Mostra una finestra di conferma prima dell'esecuzione. Passa un messaggio personalizzato o ometti per usare il prompt predefinito. |
| `authorize(string)` | Indica una capacità di policy controllata per record prima che l'handler venga eseguito. |
| `successMessage(string)` | Messaggio flash mostrato dopo un'esecuzione riuscita. Predefinito `Done.`. |

Le azioni inviano POST a un endpoint protetto. I record si risolvono tramite la stessa query base con ambito usata ovunque, quindi multi-tenancy, filtri e scope di query per risorsa si applicano automaticamente. Quando `authorize('ability')` è dichiarato, la policy viene controllata per record prima che l'handler venga eseguito. Le esecuzioni massive avvengono dentro una transazione di database e sono limitate a 100 record per richiesta. Se un record richiesto manca dal recupero con ambito, l'intera operazione abortisce con 404 invece di applicarsi silenziosamente al sottoinsieme risolto. Dichiara `authorize()` su ogni azione distruttiva.

`BulkAction::delete()` è un preset predefinito: nome `delete`, etichetta `Delete`, colore `accent`, conferma `Delete the selected records?`, e `authorize('delete')` già collegato.

## Autorizzazione

Saddle usa le policy Laravel standard. Registra una policy per un modello e il pannello la applica ovunque: indice, form, azioni di riga e selettori di relazione. Senza una policy registrata, tutte le capacità sono consentite per ogni utente autenticato. I ruoli restano nella tua applicazione: qualsiasi pacchetto di ruoli o livello fatto in casa che alimenta le tue policy funziona invariato.

### Blindare il pannello

Le risorse senza una policy registrata consentono per impostazione predefinita ogni utente autenticato. Imposta `saddle.authorization.require_policy` a `true` per fallire chiuso: le risorse senza policy diventano inaccessibili invece che aperte. Puoi anche aggiungere un middleware gate a `saddle.middleware` per un controllo globale prima che qualunque rotta del pannello venga eseguita. Se il tuo web guard è condiviso tra utenti finali e amministratori, uno di questi controlli è essenziale.

| Capacità | Dove viene controllata |
|---|---|
| `viewAny` | Pagina indice della risorsa, visibilità della sidebar |
| `create` | Form di creazione, azione store, endpoint opzioni di relazione |
| `update` | Form di modifica, azione update, link Edit per riga, endpoint opzioni di relazione (controllato su un modello fresco quando nessun record è in ambito) |
| `delete` | Azione destroy, pulsante Delete per riga |

### Visibilità dei campi con `canSee`

I singoli campi possono essere protetti per richiesta usando `canSee`. Il campo `notes` in `HorseResource` è un esempio funzionante:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

I campi nascosti vengono rimossi dal payload del form (i valori salvati non vengono mai serializzati al frontend), non contribuiscono regole di validazione, non vengono mai scritti al salvataggio e il loro endpoint opzioni di relazione restituisce 404. Il callback può essere eseguito più volte per richiesta, quindi tienilo economico e restituisci un booleano reale. Per esempio, usa `Gate::allows('view-notes', $model)` invece di `Gate::inspect(...)`, il cui oggetto `Response` è sempre truthy e non nasconderà mai il campo.

## Plugin

Un plugin è un normale pacchetto Composer. Il suo service provider registra risorse, script e stili tramite la facade `Saddle`, e l'auto-discovery dei pacchetti di Laravel lo avvia automaticamente insieme alla tua applicazione.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

Pubblica gli asset compilati dal service provider del plugin in `public/vendor/{plugin}` usando il meccanismo standard `$this->publishes([...])`, poi punta `Saddle::script()` al percorso pubblicato. Gli script e i fogli di stile del plugin sono caricati su ogni pagina del pannello dopo il bundle core del pannello.

### Elementi personalizzati

I plugin possono fornire i propri renderer di campi e colonne come elementi personalizzati. Lato PHP:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

Il pannello soddisfa questo contratto: per i campi, imposta le proprietà DOM `value` e `field` dell'elemento e ascolta un CustomEvent `saddle:input` il cui `detail` è il nuovo valore. Per le colonne, imposta le proprietà DOM `value` e `column` (sola lettura, nessun evento di input atteso).

Un elemento personalizzato vanilla minimale che implementa il contratto del campo:

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

Definisci gli elementi al livello superiore del tuo script. Il browser aggiorna qualsiasi elemento corrispondente che il pannello ha già renderizzato non appena `customElements.define` viene eseguito, quindi l'ordine di caricamento non conta mai.

Il contratto è indipendente dal framework. Funziona tutto ciò che compila in un elemento personalizzato standard: `defineCustomElement` di Vue, Lit, wrapper React o Svelte. Gli autori di plugin non sono vincolati agli interni del pannello.

## Multi-tenancy

Saddle supporta multi-tenancy opzionale con ambito URL. Abilitala puntando `tenancy.model` a qualsiasi classe Eloquent:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

Quando la multi-tenancy è attiva, il pannello viene montato sotto `/admin/{tenant}` invece di `/admin`. Il segmento `{tenant}` si risolve con lookup per chiave di rotta sul modello configurato. Chiavi tenant sconosciute restituiscono **404**. Gli utenti autenticati che non sono membri del tenant risolto vengono respinti con **403**.

### Ambito delle risorse

Dichiara la relazione BelongsTo del record verso il tenant su ogni risorsa che vuoi limitare:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

Le risorse senza `$tenant` (tabelle di lookup condivise, configurazione globale) restano senza ambito per scelta.

### Garanzie automatiche di ambito

Ogni percorso dati controlla lato server il tenant associato:

- **Indice, ricerca e filtri** passano dalla query base con ambito (`whereBelongsTo` sulla relazione dichiarata).
- **Lookup dei record** per modifica, aggiornamento e distruzione si risolvono tramite la stessa query con ambito, quindi ID cross-tenant restituiscono 404 prima che qualunque policy venga eseguita.
- **Stores** imprimono il tenant corrente lato server dopo aver riempito il form. Qualsiasi chiave esterna tenant inviata dal client viene sovrascritta.
- **Liste di opzioni di relazione** applicano lo stesso ambito quando anche la risorsa registrata del modello correlato ha ambito tenant.

### Selettore tenant

Quando l'utente autenticato appartiene a più di un tenant, la sidebar del pannello mostra un select che elenca tutte le sue appartenenze. Cambiare naviga allo stesso percorso del pannello sotto il tenant selezionato.

### Avvertenze

- **Non esporre la relazione `$tenant` come campo del form su una risorsa con ambito.** Il controller store imprime la relazione lato server, ma un campo BelongsTo modificabile che punta alla relazione tenant in un form di aggiornamento permetterebbe a un valore inviato di ripuntare il record a un tenant diverso.
- **Un'etichetta di relazione salvata continua a renderizzarsi nel form di modifica anche quando la riga correlata cade fuori dall'ambito corrente.** `BelongsTo` risolve la selezione corrente con una query senza ambito, così l'etichetta non sparisce mai dopo un cambio di ambito. Solo la lista di opzioni per nuove selezioni viene filtrata.
- **Cambiare la configurazione della multi-tenancy richiede `php artisan route:clear`**, perché il prefisso `{tenant}` viene deciso all'avvio. I server applicativi long-running (worker FPM mantenuti vivi tra le richieste) devono assicurare che lo stato della richiesta venga reimpostato tra le richieste. Il tenant associato vive sul singleton Saddle, che viene risolto fresco per richiesta con la durata predefinita del container. Quando Octane è installato, il pannello reimposta automaticamente il tenant associato tramite gli hook del ciclo di vita delle richieste di Octane.

## Configurazione

`saddle:install` pubblica `config/saddle.php`. Chiavi disponibili:

| Chiave | Predefinito | Descrizione |
|---|---|---|
| `path` | `'admin'` | Prefisso URL per il pannello (per es. `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Stack middleware applicato a tutte le rotte del pannello. |
| `resources.path` | `app_path('Saddle')` | Percorso del filesystem scansionato per classi di risorse. |
| `resources.namespace` | `'App\\Saddle'` | Namespace PHP corrispondente a `resources.path`. |
| `per_page` | `25` | Righe predefinite per pagina nelle tabelle indice. |
| `brand.name` | `'Saddle'` | Nome del pannello (sidebar e scheda del browser). |
| `brand.accent` | `'#d9501f'` | Colore accent (pulsanti, stati attivi). |
| `uploads.disk` | `'public'` | Disco filesystem predefinito usato dai campi `FileUpload` quando non è impostato un `disk()` per campo. |
| `uploads.directory` | `'saddle'` | Directory di upload predefinita dentro il disco quando non è impostato un `directory()` per campo. |

## Comandi

| Comando | Descrizione |
|---|---|
| `saddle:install` | Pubblica la configurazione, pubblica gli asset del pannello, crea `app/Saddle/`. Offre di aggiungere `saddle:upgrade` a `composer post-update-cmd` così gli asset restano aggiornati. |
| `saddle:upgrade` | Ripubblica gli asset del pannello. Esegui dopo ogni aggiornamento del pacchetto. |
| `saddle:resource NameResource --model=Name` | Crea lo scaffold di una nuova classe di risorsa. L'opzione `--model` è facoltativa. Viene dedotta dal nome della risorsa quando omessa. |

**Nota di deploy.** Aggiungi `php artisan saddle:upgrade` al tuo script di deploy dopo `composer install` o `composer update`. Il pannello mostra un banner di avviso nella UI quando gli asset pubblicati non sono allineati con la versione del pacchetto installata.

## Sviluppo locale

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

La directory `workbench/` contiene un'applicazione host minima usata dalla suite di test e per prove manuali. `vendor/bin/testbench serve` la avvia con `HorseResource` registrato. Nota che le rotte del pannello stanno dietro il middleware `auth` e il workbench include solo una rotta stub `/login`, quindi per navigare interattivamente imposta temporaneamente `'middleware' => ['web']` in `config/saddle.php` oppure usa i feature test. Non c'è ancora un seeder demo.

## Stack

Costruito per **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## Licenza

MIT.
