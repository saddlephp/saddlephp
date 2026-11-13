<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../art/logo-dark.png">
    <img src="../../art/logo.png" alt="Saddle" width="220">
  </picture>
</p>

<h3 align="center">Das Open-Source-Framework für Admin-Panels in Laravel, gebaut für Inertia und Vue.</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>Dokumentation</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>Änderungsprotokoll</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>Mitwirken</b></a>
</p>

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

<br>

<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/dashboard-dark.png">
    <img src="../../.github/screenshots/dashboard-light.png" alt="Das Saddle-Dashboard mit Kennzahlen-Widgets und einem Diagramm" width="100%">
  </picture>
</p>

<p align="center"><em>Sattel auf, Cowboy, in der Stadt gibt es ein neues Admin-Panel.</em></p>

## Was ist Saddle?

Saddle macht aus deinen Eloquent-Modellen ein ausgereiftes Admin-Panel. Du beschreibst jedes Modell einmal als PHP-Ressourcenklasse mit einem Formular und einer Tabelle, und Saddle erzeugt daraus die Listen-, Erstell-, Bearbeitungs- und Detailansichten samt Suche, Filtern und Aktionen. Gerendert wird mit Inertia und Vue, die Anmeldung und die Richtlinien deiner bestehenden Laravel-App werden mitgenutzt, und der Rest deiner Anwendung bleibt unberührt.

<table>
<tr>
<td width="50%" valign="middle">

### Tabellen, die die Arbeit erledigen

Sortier- und durchsuchbare Spalten mit Badges und Booleans, Filter, Zeilenaktionen und Massenaktionen mit Bestätigung. Exportiere die aktuelle Ansicht als CSV oder importiere eine CSV-Datei über dieselbe Validierung, die auch dein Formular verwendet.

[Tabellen und Spalten →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="Eine Saddle-Ressourcentabelle mit Filtern und einer Leiste für Massenaktionen">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="Ein Saddle-Bearbeitungsformular mit einem Abschnitt, einem zweispaltigen Raster und Tabs">
</picture>
</td>
<td width="50%" valign="middle">

### Formulare mit echtem Layout

Elf Feldtypen, von Text und Auswahllisten bis zu durchsuchbaren Beziehungsauswahlen, Datei-Uploads und Markdown. Ordne sie in Abschnitten, Rastern und Tabs an. Validierung, Sichtbarkeit und Speichern richten sich alle nach denselben Felddefinitionen.

[Felder →](../sections/fields.md) · [Formularlayout →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### Eine Klasse pro Modell

Eine Ressource ist schlichtes PHP. Lege sie in `app/Saddle/` ab, und sie wird beim Start automatisch erkannt: keine Routen, Controller oder Vue-Dateien zu schreiben. Der Zugriff folgt deinen Laravel-Richtlinien und ist standardmäßig gesperrt.

[Ressourcen →](../sections/resources.md) · [Autorisierung →](../sections/authorization.md)

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

## Funktionen

<table>
<tr>
<td width="33%" valign="top">

**Ressourcen**
- Automatisch erkannte Ressourcenklassen
- Seiten zum Auflisten, Erstellen, Bearbeiten und reinen Ansehen
- Generator `saddle:resource`
- Eager Loading und Beziehungsspalten

</td>
<td width="33%" valign="top">

**Formulare**
- Text, Passwort, Textbereich, Zahl, Auswahl, Schalter
- Datum, Datum mit Uhrzeit, Markdown, Datei-Upload
- Durchsuchbare `BelongsTo`-Auswahl
- Abschnitte, Raster und Tabs

</td>
<td width="33%" valign="top">

**Tabellen**
- Text-, Badge- und Boolean-Spalten
- Sortierung, Suche und `formatUsing`
- Auswahl- und Boolean-Filter
- Seitenumbruch

</td>
</tr>
<tr>
<td valign="top">

**Aktionen**
- Zeilenaktionen und Massenaktionen
- Bestätigungsdialoge
- Richtlinienprüfung pro Datensatz
- Massenläufe in Transaktionen

</td>
<td valign="top">

**Beziehungen**
- Beziehungsmanager für `HasMany`
- Verknüpfte Datensätze direkt anlegen, bearbeiten und löschen
- Auf den übergeordneten Datensatz beschränkt

</td>
<td valign="top">

**Dashboard**
- Kennzahlen-Widgets mit Mini-Diagrammen
- Diagramm-Widgets
- Datenbankbenachrichtigungen mit Glocke
- Globale Suche über alle Ressourcen

</td>
</tr>
<tr>
<td valign="top">

**Zugriff**
- Standardmäßige Laravel-Richtlinien
- Standardmäßig gesperrt (fail-closed)
- Sichtbarkeit pro Feld mit `canSee`
- Nutzt deine bestehende Anmeldung

</td>
<td valign="top">

**Mandantenfähigkeit**
- Mandanten über die URL (`/admin/{tenant}`)
- Automatische Eingrenzung von Abfragen und Zuordnung zum Mandanten
- Mandantenwechsler und Selbstregistrierung
- Zugriffs-Hook für die Abrechnung

</td>
<td valign="top">

**Daten**
- CSV-Export der aktuellen Ansicht
- CSV-Import über die Formularvalidierung
- Soft Deletes mit Wiederherstellen und endgültigem Löschen

</td>
</tr>
<tr>
<td valign="top">

**Aussehen und Sprache**
- Helles, dunkles und System-Theme
- Markenname, Akzentfarbe und Theme-Tokens
- Panel in 14 Sprachen übersetzt
- Unterstützung für CSP-Nonces

</td>
<td valign="top">

**Plugins**
- Gewöhnliche Composer-Pakete
- Eigene Felder und Spalten als Web Components
- Hooks für geteilte Props, Navigation und Theme-Tokens

</td>
<td valign="top">

**Entwicklererfahrung**
- Testhelfer für Pest und PHPUnit
- `saddle:install` und `saddle:upgrade`
- Warnung bei veralteten Assets nach Upgrades
- Laravel 11, 12 und 13

</td>
</tr>
</table>

## Gebaut mit

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## Schnellstart

Erfordert PHP 8.4+ und Laravel 11.48+, 12 oder 13.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Melde dich über die Anmeldung deiner App an, registriere eine Richtlinie für das Modell und öffne dann `/admin`. Führe nach jedem Paketupdate `php artisan saddle:upgrade` aus. Die [Einstiegsanleitung](../sections/getting-started.md) führt dich Schritt für Schritt durch alles.

## Mehr erfahren

| | |
|---|---|
| **Entwickeln** | [Ressourcen](../sections/resources.md) · [Felder](../sections/fields.md) · [Formularlayout](../sections/forms-layout.md) · [Tabellen](../sections/tables.md) · [Filter](../sections/filters.md) · [Aktionen](../sections/actions.md) · [Beziehungsmanager](../sections/relations.md) |
| **Panel** | [Dashboard-Widgets](../sections/widgets.md) · [Globale Suche](../sections/search.md) · [Benachrichtigungen](../sections/notifications.md) · [Soft Deletes](../sections/soft-deletes.md) · [Import und Export](../sections/import-export.md) |
| **Betrieb** | [Autorisierung](../sections/authorization.md) · [Mandantenfähigkeit](../sections/tenancy.md) · [Mandanten-Extras](../sections/tenancy-extras.md) · [Konfiguration](../sections/configuration.md) · [Theming](../sections/theming.md) · [Lokalisierung](../sections/i18n.md) |
| **Erweitern** | [Plugins](../sections/plugins.md) · [Testhelfer](../sections/testing.md) · [Mitwirken](../../CONTRIBUTING.md) · [Sicherheit](../../SECURITY.md) |

## Lizenz

Saddle ist Open-Source-Software unter der [MIT-Lizenz](../../LICENSE).
