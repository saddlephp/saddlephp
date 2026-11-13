<p align="center">
  <img src=".github/og.png" alt="Saddle, there's a new admin panel in town" width="820">
</p>

<h3 align="center">The open-source admin panel framework for Laravel, built for Inertia and Vue.</h3>

<p align="center">
  <a href="docs/sections/getting-started.md"><b>Docs</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>Changelog</b></a> ·
  <a href="CONTRIBUTING.md"><b>Contributing</b></a>
</p>

<p align="center">
  <a href="docs/translations/README.ar.md">العربية</a> •
  <a href="docs/translations/README.de.md">Deutsch</a> •
  <b>English</b> •
  <a href="docs/translations/README.es.md">Español</a> •
  <a href="docs/translations/README.fr.md">Français</a> •
  <a href="docs/translations/README.it.md">Italiano</a> •
  <a href="docs/translations/README.ja.md">日本語</a> •
  <a href="docs/translations/README.ko.md">한국어</a> •
  <a href="docs/translations/README.nl.md">Nederlands</a> •
  <a href="docs/translations/README.pl.md">Polski</a> •
  <a href="docs/translations/README.pt-BR.md">Português (BR)</a> •
  <a href="docs/translations/README.ru.md">Русский</a> •
  <a href="docs/translations/README.tr.md">Türkçe</a> •
  <a href="docs/translations/README.zh-CN.md">简体中文</a>
</p>

## What is Saddle?

Saddle turns your Eloquent models into a polished admin panel. You describe each model once, as a PHP resource class with a form and a table, and Saddle builds the list, create, edit and view screens, the search, the filters and the actions. It renders with Inertia and Vue, uses your existing Laravel login and policies, and stays out of the rest of your app.

<table>
<tr>
<td width="50%" valign="middle">

### Tables that do the work

Sortable, searchable columns with badges and booleans, filters, row actions and bulk actions with confirmation. Export the current view to CSV, or import a CSV through the same validation your form uses.

[Tables and columns →](docs/sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset=".github/screenshots/table-dark.png">
  <img src=".github/screenshots/table-light.png" alt="A Saddle resource table with filters and a bulk-action bar">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset=".github/screenshots/form-dark.png">
  <img src=".github/screenshots/form-light.png" alt="A Saddle edit form with a section, a two-column grid and tabs">
</picture>
</td>
<td width="50%" valign="middle">

### Forms with real layout

Eleven field types, from text and selects to searchable relation pickers, file uploads and Markdown. Arrange them in sections, grids and tabs. Validation, visibility and saving all follow the same field definitions.

[Fields →](docs/sections/fields.md) · [Form layout →](docs/sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### One class per model

A resource is plain PHP. Drop it in `app/Saddle/` and it is discovered at boot: no routes, controllers or Vue files to write. Access follows your Laravel policies and is closed by default.

[Resources →](docs/sections/resources.md) · [Authorization →](docs/sections/authorization.md)

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

## Features

<table>
<tr>
<td width="33%" valign="top">

**Resources**
- Auto-discovered resource classes
- List, create, edit and read-only view pages
- `saddle:resource` generator
- Eager loading and relation columns

</td>
<td width="33%" valign="top">

**Forms**
- Text, password, textarea, number, select, toggle
- Date, date-time, Markdown, file upload
- Searchable `BelongsTo` picker
- Sections, grids and tabs

</td>
<td width="33%" valign="top">

**Tables**
- Text, badge and boolean columns
- Sorting, search and `formatUsing`
- Select and boolean filters
- Pagination

</td>
</tr>
<tr>
<td valign="top">

**Actions**
- Row actions and bulk actions
- Confirmation dialogs
- Per-record policy checks
- Transactional bulk runs

</td>
<td valign="top">

**Relations**
- `HasMany` relation managers
- Create, edit and delete related records in place
- Scoped to the parent record

</td>
<td valign="top">

**Dashboard**
- Stat widgets with mini charts
- Chart widgets
- Database notifications with a bell
- Global search across resources

</td>
</tr>
<tr>
<td valign="top">

**Access**
- Standard Laravel policies
- Fail-closed by default
- Per-field visibility with `canSee`
- Uses your existing login

</td>
<td valign="top">

**Multi-tenancy**
- URL-scoped tenants (`/admin/{tenant}`)
- Automatic query scoping and tenant stamping
- Tenant switcher and self-registration
- Access gate hook for billing

</td>
<td valign="top">

**Data**
- CSV export of the current view
- CSV import through form validation
- Soft deletes with restore and force delete

</td>
</tr>
<tr>
<td valign="top">

**Look and language**
- Light, dark and system themes
- Brand name, accent and theme tokens
- Panel translated into 14 languages
- CSP nonce support

</td>
<td valign="top">

**Plugins**
- Plain Composer packages
- Custom fields and columns as web components
- Hooks for shared props, navigation and theme tokens

</td>
<td valign="top">

**Developer experience**
- Pest and PHPUnit testing helpers
- `saddle:install` and `saddle:upgrade`
- Stale-asset warning after upgrades
- Laravel 11, 12 and 13

</td>
</tr>
</table>

## Built with

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## Quick start

Requires PHP 8.4+ and Laravel 11.48+, 12 or 13.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Sign in through your app's login, register a policy for the model, then visit `/admin`. Run `php artisan saddle:upgrade` after each package update. The [getting started guide](docs/sections/getting-started.md) walks through each step.

## Learn more

| | |
|---|---|
| **Building** | [Resources](docs/sections/resources.md) · [Fields](docs/sections/fields.md) · [Form layout](docs/sections/forms-layout.md) · [Tables](docs/sections/tables.md) · [Filters](docs/sections/filters.md) · [Actions](docs/sections/actions.md) · [Relation managers](docs/sections/relations.md) |
| **Panel** | [Dashboard widgets](docs/sections/widgets.md) · [Global search](docs/sections/search.md) · [Notifications](docs/sections/notifications.md) · [Soft deletes](docs/sections/soft-deletes.md) · [Import and export](docs/sections/import-export.md) |
| **Running it** | [Authorization](docs/sections/authorization.md) · [Multi-tenancy](docs/sections/tenancy.md) · [Tenancy extras](docs/sections/tenancy-extras.md) · [Configuration](docs/sections/configuration.md) · [Theming](docs/sections/theming.md) · [Localization](docs/sections/i18n.md) |
| **Extending** | [Plugins](docs/sections/plugins.md) · [Testing helpers](docs/sections/testing.md) · [Contributing](CONTRIBUTING.md) · [Security](SECURITY.md) |

## License

Saddle is open-source software licensed under the [MIT license](LICENSE).
