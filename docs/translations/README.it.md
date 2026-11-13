<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../art/logo-dark.png">
    <img src="../../art/logo.png" alt="Saddle" width="220">
  </picture>
</p>

<h3 align="center">Il framework open source per pannelli di amministrazione in Laravel, pensato per Inertia e Vue.</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>Documentazione</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>Novità</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>Contribuire</b></a>
</p>

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

<br>

<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/dashboard-dark.png">
    <img src="../../.github/screenshots/dashboard-light.png" alt="La dashboard di Saddle con widget statistici e un grafico" width="100%">
  </picture>
</p>

<p align="center"><em>In sella, cowboy: c'è un nuovo pannello di amministrazione in città.</em></p>

## Che cos'è Saddle?

Saddle trasforma i tuoi modelli Eloquent in un pannello di amministrazione curato. Descrivi ogni modello una sola volta, con una classe resource PHP che contiene un form e una tabella, e Saddle costruisce le schermate di elenco, creazione, modifica e visualizzazione, oltre a ricerca, filtri e azioni. Il rendering avviene con Inertia e Vue, sfrutta il login e le policy Laravel che hai già e non interferisce con il resto dell'applicazione.

<table>
<tr>
<td width="50%" valign="middle">

### Tabelle che fanno il lavoro per te

Colonne ordinabili e ricercabili con badge e valori booleani, filtri, azioni per riga e azioni di massa con conferma. Esporta la vista corrente in CSV, oppure importa un CSV passando dalla stessa validazione usata dal form.

[Tabelle e colonne →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="Una tabella di resource di Saddle con filtri e una barra delle azioni di massa">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="Un form di modifica di Saddle con una sezione, una griglia a due colonne e delle schede">
</picture>
</td>
<td width="50%" valign="middle">

### Form con un vero layout

Undici tipi di campo, dal testo e dalle select fino ai selettori di relazioni con ricerca, al caricamento di file e al Markdown. Organizzali in sezioni, griglie e schede. Validazione, visibilità e salvataggio seguono tutti le stesse definizioni dei campi.

[Campi →](../sections/fields.md) · [Layout dei form →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### Una classe per modello

Una resource è semplice PHP. Mettila in `app/Saddle/` e viene rilevata all'avvio: nessuna rotta, controller o file Vue da scrivere. L'accesso segue le tue policy Laravel ed è chiuso per impostazione predefinita.

[Resource →](../sections/resources.md) · [Autorizzazione →](../sections/authorization.md)

</td>
<td width="50%">

```php
class HorseResource extends Resource
{
    public static string $model = Horse::class;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name')->required(),
            BelongsTo::make('rider')->searchable(),
            Toggle::make('is_saddled'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->sortable()->searchable(),
            BooleanColumn::make('is_saddled'),
        ]);
    }
}
```

</td>
</tr>
</table>

## Funzionalità

<table>
<tr>
<td width="33%" valign="top">

**Resource**
- Classi resource rilevate automaticamente
- Pagine di elenco, creazione, modifica e sola lettura
- Generatore `saddle:resource`
- Eager loading e colonne di relazione

</td>
<td width="33%" valign="top">

**Form**
- Testo, password, textarea, numero, select, interruttore
- Data, data e ora, Markdown, caricamento file
- Selettore `BelongsTo` con ricerca
- Sezioni, griglie e schede

</td>
<td width="33%" valign="top">

**Tabelle**
- Colonne di testo, badge e booleane
- Ordinamento, ricerca e `formatUsing`
- Filtri a selezione e booleani
- Paginazione

</td>
</tr>
<tr>
<td valign="top">

**Azioni**
- Azioni per riga e azioni di massa
- Finestre di conferma
- Controlli delle policy per singolo record
- Esecuzioni di massa in transazione

</td>
<td valign="top">

**Relazioni**
- Gestori di relazioni `HasMany`
- Creazione, modifica ed eliminazione dei record collegati sul posto
- Limitate al record padre

</td>
<td valign="top">

**Dashboard**
- Widget statistici con mini grafici
- Widget con grafici
- Notifiche da database con campanella
- Ricerca globale su tutte le resource

</td>
</tr>
<tr>
<td valign="top">

**Accesso**
- Policy Laravel standard
- Chiuso per impostazione predefinita
- Visibilità per campo con `canSee`
- Usa il login che hai già

</td>
<td valign="top">

**Multi-tenancy**
- Tenant definiti dall'URL (`/admin/{tenant}`)
- Filtro automatico delle query e assegnazione del tenant
- Selettore del tenant e registrazione autonoma
- Hook di controllo accesso per la fatturazione

</td>
<td valign="top">

**Dati**
- Esportazione CSV della vista corrente
- Importazione CSV tramite la validazione del form
- Soft delete con ripristino ed eliminazione definitiva

</td>
</tr>
<tr>
<td valign="top">

**Aspetto e lingua**
- Temi chiaro, scuro e di sistema
- Nome del brand, colore di accento e token del tema
- Pannello tradotto in 14 lingue
- Supporto per nonce CSP

</td>
<td valign="top">

**Plugin**
- Semplici pacchetti Composer
- Campi e colonne personalizzati come web component
- Hook per props condivise, navigazione e token del tema

</td>
<td valign="top">

**Esperienza di sviluppo**
- Helper di test per Pest e PHPUnit
- `saddle:install` e `saddle:upgrade`
- Avviso di asset obsoleti dopo gli aggiornamenti
- Laravel 11, 12 e 13

</td>
</tr>
</table>

## Realizzato con

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## Avvio rapido

Richiede PHP 8.4+ e Laravel 11.48+, 12 o 13.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Accedi tramite il login della tua applicazione, registra una policy per il modello e poi visita `/admin`. Esegui `php artisan saddle:upgrade` dopo ogni aggiornamento del pacchetto. La [guida introduttiva](../sections/getting-started.md) illustra ogni passaggio.

## Per saperne di più

| | |
|---|---|
| **Sviluppo** | [Resource](../sections/resources.md) · [Campi](../sections/fields.md) · [Layout dei form](../sections/forms-layout.md) · [Tabelle](../sections/tables.md) · [Filtri](../sections/filters.md) · [Azioni](../sections/actions.md) · [Gestori di relazioni](../sections/relations.md) |
| **Pannello** | [Widget della dashboard](../sections/widgets.md) · [Ricerca globale](../sections/search.md) · [Notifiche](../sections/notifications.md) · [Soft delete](../sections/soft-deletes.md) · [Importazione ed esportazione](../sections/import-export.md) |
| **In produzione** | [Autorizzazione](../sections/authorization.md) · [Multi-tenancy](../sections/tenancy.md) · [Extra per la multi-tenancy](../sections/tenancy-extras.md) · [Configurazione](../sections/configuration.md) · [Temi](../sections/theming.md) · [Localizzazione](../sections/i18n.md) |
| **Estensione** | [Plugin](../sections/plugins.md) · [Helper di test](../sections/testing.md) · [Contribuire](../../CONTRIBUTING.md) · [Sicurezza](../../SECURITY.md) |

## Licenza

Saddle è software open source distribuito con [licenza MIT](../../LICENSE).
