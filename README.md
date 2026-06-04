<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="art/logo-dark.png">
    <img src="art/logo.png" alt="SaddlePHP" width="340">
  </picture>
  <br>
  <em>Saddle up cowboy, there's a new admin panel in town.</em>
</p>

---

**SaddlePHP** is the open-source admin panel framework for Laravel, built the modern way for **Inertia and
Vue**. Round up your Eloquent models into polished resource panels, with form and table builders, roles and access,
plugins, and multi-tenancy.

> **Status: v0.3 ships table filters and searchable relation pickers.** v0.3 adds `SelectFilter` and `BooleanFilter` for the index table, and `searchable()` plus `modifyOptionsQuery()` on `BelongsTo` for async, scope-aware relation selects. The marketing site lives at **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## Installation

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

The service provider is auto-discovered. `saddle:install` publishes the config file, publishes panel assets, and creates `app/Saddle/` for your resource classes. Visit `/admin` to see the panel.

## Define a resource

Place resource classes in `app/Saddle/`. Each class extends `SaddlePHP\Resource` and implements `form()` and `table()`.

```php
<?php

declare(strict_types=1);

namespace App\Saddle;

use App\Models\Horse;
use SaddlePHP\Fields\BelongsTo;
use SaddlePHP\Fields\Date;
use SaddlePHP\Fields\Number;
use SaddlePHP\Fields\Select;
use SaddlePHP\Fields\Text;
use SaddlePHP\Fields\Textarea;
use SaddlePHP\Fields\Toggle;
use SaddlePHP\Forms\Form;
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
            Text::make('name')->required()->rules('max:120'),
            Select::make('breed')->options([
                'quarter' => 'Quarter Horse',
                'mustang' => 'Mustang',
                'appaloosa' => 'Appaloosa',
            ]),
            Textarea::make('notes')->rows(3),
            Toggle::make('is_saddled'),
            BelongsTo::make('rider')->searchable(),
            Number::make('age')->integer()->min(0)->max(50),
            Date::make('foaled_on'),
        ]);
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

Resources are discovered automatically by scanning `app/Saddle/` at boot — no manual registration needed.

## Fields

| Field | Description |
|---|---|
| `Text` | Single-line text input. Modifiers: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | Multi-line text input. Modifiers: `rows(int)`. |
| `Select` | Fixed-options dropdown. Pass an associative array to `options(['value' => 'Label'])`. |
| `Toggle` | Boolean switch. Stores `true`/`false`. |
| `BelongsTo` | Relation select. The argument is the Eloquent relation method name on the model (`BelongsTo::make('rider')` reads `$model->rider()` and submits the foreign key). Option labels resolve from `titleAttribute('name')`, falling back to the related model's registered resource `$title`, then its key. If neither a `titleAttribute()` nor a registered resource is available, options are labeled by primary key, so set `titleAttribute('name')` for readable labels. Options are capped at 100 by default; override with `limit(int)`. `searchable()` switches to an async picker that searches the related table as you type via an authenticated endpoint; on edit, only the current selection is embedded (the full list is not loaded). `modifyOptionsQuery(fn ($query) => ...)` scopes the option list for tenancy or visibility; it applies to listed and searched options, while a record's saved selection keeps rendering its label even when it falls outside the scope. |
| `Number` | Numeric input. Modifiers: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | Date input. Values render as `Y-m-d`. |

## Columns

| Column | Description |
|---|---|
| `TextColumn` | Renders the raw attribute value. Modifiers: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (formats DateTime attributes; default format `Y-m-d H:i`). |
| `BadgeColumn` | Renders a pill badge. Use `colors(['value' => 'token'])` to map option values to color tokens (`accent`, `ink`, `muted`). |
| `BooleanColumn` | Renders a check mark for truthy values and a dash for falsy ones. |

**Relation columns and eager loading.** Dotted names like `TextColumn::make('rider.name')` read through a loaded relation. Declare `public static array $with = ['rider']` on the resource so the index query eager-loads the relation before rendering. Relation columns are not sortable or searchable yet.

## Filters

Filters are declared on the table via `->filters([...])`. On the index, the panel applies them from `filter[name]=value` query string parameters. Requested values are validated against the declaration, so unknown filter names and undeclared option values are silently ignored.

| Filter | Description |
|---|---|
| `SelectFilter` | Exact-match dropdown. `options(['value' => 'Label'])` defines both the dropdown choices and the allowlist of accepted values. |
| `BooleanFilter` | Yes/No dropdown over a boolean column. |

## Configuration

`saddle:install` publishes `config/saddle.php`. Available keys:

| Key | Default | Description |
|---|---|---|
| `path` | `'admin'` | URL prefix for the panel (e.g. `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Middleware stack applied to all panel routes. |
| `resources.path` | `app_path('Saddle')` | Filesystem path scanned for resource classes. |
| `resources.namespace` | `'App\\Saddle'` | PHP namespace corresponding to `resources.path`. |
| `per_page` | `25` | Default rows per page on index tables. |
| `brand.name` | `'SaddlePHP'` | Panel name (sidebar + browser tab). |
| `brand.accent` | `'#d9501f'` | Accent colour (buttons, active states). |

## Commands

| Command | Description |
|---|---|
| `saddle:install` | Publish config, publish panel assets, create `app/Saddle/`. Offers to add `saddle:upgrade` to `composer post-update-cmd` so assets stay fresh. |
| `saddle:upgrade` | Re-publish panel assets. Run after every package update. |
| `saddle:resource NameResource --model=Name` | Scaffold a new resource class. The `--model` option is optional; it is inferred from the resource name when omitted. |

## Local development

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

The `workbench/` directory contains a minimal host application used by the test suite and for manual poking. `vendor/bin/testbench serve` boots it with `HorseResource` registered; note that panel routes sit behind the `auth` middleware and the workbench ships only a stub `/login` route, so for interactive browsing either temporarily set `'middleware' => ['web']` in `config/saddle.php` or browse through the feature tests instead. There is no demo seeder yet.

## Roadmap

- [x] Resource panels (CRUD from an Eloquent model)
- [x] Form builder
- [x] Table builder
- [x] Relations (BelongsTo)
- [x] Table filters
- [ ] Roles and access (policy-driven)
- [ ] Plugins
- [ ] Multi-tenancy

## Stack

Built for **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## License

MIT.
