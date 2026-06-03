<p align="center">
  <strong>SaddlePHP</strong><br>
  <em>Saddle up cowboy, there's a new admin panel in town.</em>
</p>

---

**SaddlePHP** is the open-source admin panel framework for Laravel, built the modern way for **Inertia and
Vue**. Round up your Eloquent models into polished resource panels, with form and table builders, roles and access,
plugins, and multi-tenancy.

> **Status: v0.1 core loop ships.** Resource panels with full CRUD, form builder, table builder (search, sort, paginate), install and upgrade commands, and the Inertia+Vue panel shell are all working. The marketing site lives at **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

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

use SaddlePHP\Fields\Select;
use SaddlePHP\Fields\Text;
use SaddlePHP\Fields\Textarea;
use SaddlePHP\Fields\Toggle;
use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;
use App\Models\Horse;

class HorseResource extends Resource
{
    public static string $model = Horse::class;

    public static ?string $title = 'name';

    public static ?string $icon = 'collection';

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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->sortable()->searchable(),
            TextColumn::make('breed')->sortable(),
            TextColumn::make('created_at')->sortable(),
        ]);
    }
}
```

Resources are discovered automatically by scanning `app/Saddle/` at boot — no manual registration needed.

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
- [ ] Roles and access (policy-driven)
- [ ] Plugins
- [ ] Multi-tenancy

## Stack

Built for **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## License

MIT.
