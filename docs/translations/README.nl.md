<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../art/logo-dark.png">
    <img src="../../art/logo.png" alt="Saddle" width="220">
  </picture>
</p>

<h3 align="center">Het open-source framework voor beheerpanelen in Laravel, gebouwd voor Inertia en Vue.</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>Documentatie</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>Wijzigingen</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>Bijdragen</b></a>
</p>

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

<br>

<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/dashboard-dark.png">
    <img src="../../.github/screenshots/dashboard-light.png" alt="Het Saddle-dashboard met statistiekwidgets en een grafiek" width="100%">
  </picture>
</p>

<p align="center"><em>Zadel op, cowboy: er is een nieuw beheerpaneel in de stad.</em></p>

## Wat is Saddle?

Saddle maakt van je Eloquent-modellen een verzorgd beheerpaneel. Je beschrijft elk model één keer, als PHP-resourceklasse met een formulier en een tabel, en Saddle bouwt de overzichts-, aanmaak-, bewerk- en detailschermen, de zoekfunctie, de filters en de acties. Alles wordt gerenderd met Inertia en Vue, gebruikt je bestaande Laravel-login en policies, en blijft uit de buurt van de rest van je app.

<table>
<tr>
<td width="50%" valign="middle">

### Tabellen die het werk doen

Sorteerbare, doorzoekbare kolommen met badges en booleans, filters, rijacties en bulkacties met bevestiging. Exporteer de huidige weergave naar CSV, of importeer een CSV via dezelfde validatie die je formulier gebruikt.

[Tabellen en kolommen →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="Een Saddle-resourcetabel met filters en een balk voor bulkacties">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="Een Saddle-bewerkformulier met een sectie, een raster van twee kolommen en tabbladen">
</picture>
</td>
<td width="50%" valign="middle">

### Formulieren met een echte lay-out

Elf veldtypen, van tekst en keuzelijsten tot doorzoekbare relatiekiezers, bestandsuploads en Markdown. Deel ze in met secties, rasters en tabbladen. Validatie, zichtbaarheid en opslaan volgen allemaal dezelfde velddefinities.

[Velden →](../sections/fields.md) · [Formulierlay-out →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### Eén klasse per model

Een resource is gewoon PHP. Zet hem in `app/Saddle/` en hij wordt bij het opstarten gevonden: geen routes, controllers of Vue-bestanden om te schrijven. Toegang volgt je Laravel-policies en staat standaard dicht.

[Resources →](../sections/resources.md) · [Autorisatie →](../sections/authorization.md)

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

## Functies

<table>
<tr>
<td width="33%" valign="top">

**Resources**
- Automatisch gevonden resourceklassen
- Overzichts-, aanmaak-, bewerk- en alleen-lezenpagina's
- `saddle:resource`-generator
- Eager loading en relatiekolommen

</td>
<td width="33%" valign="top">

**Formulieren**
- Tekst, wachtwoord, tekstvak, getal, keuzelijst, schakelaar
- Datum, datum en tijd, Markdown, bestandsupload
- Doorzoekbare `BelongsTo`-kiezer
- Secties, rasters en tabbladen

</td>
<td width="33%" valign="top">

**Tabellen**
- Tekst-, badge- en booleankolommen
- Sorteren, zoeken en `formatUsing`
- Keuze- en booleanfilters
- Paginering

</td>
</tr>
<tr>
<td valign="top">

**Acties**
- Rijacties en bulkacties
- Bevestigingsdialogen
- Policycontroles per record
- Bulkuitvoering binnen een transactie

</td>
<td valign="top">

**Relaties**
- `HasMany`-relatiebeheerders
- Gerelateerde records ter plekke aanmaken, bewerken en verwijderen
- Beperkt tot het bovenliggende record

</td>
<td valign="top">

**Dashboard**
- Statistiekwidgets met minigrafieken
- Grafiekwidgets
- Databasemeldingen met een belletje
- Globaal zoeken over alle resources

</td>
</tr>
<tr>
<td valign="top">

**Toegang**
- Standaard Laravel-policies
- Standaard dicht
- Zichtbaarheid per veld met `canSee`
- Gebruikt je bestaande login

</td>
<td valign="top">

**Multi-tenancy**
- Tenants afgebakend via de URL (`/admin/{tenant}`)
- Automatische queryafbakening en tenanttoewijzing
- Tenantwisselaar en zelfregistratie
- Toegangshook voor facturering

</td>
<td valign="top">

**Data**
- CSV-export van de huidige weergave
- CSV-import via formuliervalidatie
- Soft deletes met herstellen en definitief verwijderen

</td>
</tr>
<tr>
<td valign="top">

**Uiterlijk en taal**
- Lichte, donkere en systeemthema's
- Merknaam, accentkleur en themetokens
- Paneel vertaald in 14 talen
- Ondersteuning voor CSP-nonces

</td>
<td valign="top">

**Plugins**
- Gewone Composer-pakketten
- Eigen velden en kolommen als webcomponenten
- Hooks voor gedeelde props, navigatie en themetokens

</td>
<td valign="top">

**Ontwikkelaarservaring**
- Testhelpers voor Pest en PHPUnit
- `saddle:install` en `saddle:upgrade`
- Waarschuwing voor verouderde assets na upgrades
- Laravel 11, 12 en 13

</td>
</tr>
</table>

## Gebouwd met

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## Snel aan de slag

Vereist PHP 8.4+ en Laravel 11.48+, 12 of 13.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Log in via de login van je app, registreer een policy voor het model en ga daarna naar `/admin`. Voer `php artisan saddle:upgrade` uit na elke update van het pakket. De [handleiding om te beginnen](../sections/getting-started.md) loopt elke stap met je door.

## Meer weten

| | |
|---|---|
| **Bouwen** | [Resources](../sections/resources.md) · [Velden](../sections/fields.md) · [Formulierlay-out](../sections/forms-layout.md) · [Tabellen](../sections/tables.md) · [Filters](../sections/filters.md) · [Acties](../sections/actions.md) · [Relatiebeheerders](../sections/relations.md) |
| **Paneel** | [Dashboardwidgets](../sections/widgets.md) · [Globaal zoeken](../sections/search.md) · [Meldingen](../sections/notifications.md) · [Soft deletes](../sections/soft-deletes.md) · [Importeren en exporteren](../sections/import-export.md) |
| **In gebruik** | [Autorisatie](../sections/authorization.md) · [Multi-tenancy](../sections/tenancy.md) · [Extra's voor tenancy](../sections/tenancy-extras.md) · [Configuratie](../sections/configuration.md) · [Thema's](../sections/theming.md) · [Lokalisatie](../sections/i18n.md) |
| **Uitbreiden** | [Plugins](../sections/plugins.md) · [Testhelpers](../sections/testing.md) · [Bijdragen](../../CONTRIBUTING.md) · [Beveiliging](../../SECURITY.md) |

## Licentie

Saddle is open-sourcesoftware, uitgebracht onder de [MIT-licentie](../../LICENSE).
