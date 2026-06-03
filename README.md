<p align="center">
  <strong>RodeoPHP</strong><br>
  <em>Saddle up cowboy, there's a new CMF in town.</em>
</p>

---

**RodeoPHP** is the open-source admin and content framework for Laravel, built the modern way for **Inertia and
Vue**. Round up your Eloquent models into polished resource panels, with form and table builders, roles and access,
plugins, and multi-tenancy.

> **Status: v0.1 core loop ships.** Resource panels with full CRUD, form builder, table builder (search, sort, paginate), install and upgrade commands, and the Inertia+Vue panel shell are all working. The marketing site lives at **[rodeophp.com](https://rodeophp.com)** ([RodeoPHP/rodeophp.com](https://github.com/RodeoPHP/rodeophp.com)).

## Installation

```bash
composer require rodeophp/rodeophp
php artisan rodeo:install
php artisan rodeo:resource HorseResource --model=Horse
```

The service provider is auto-discovered. `rodeo:install` publishes the config file, publishes panel assets, and creates `app/Rodeo/` for your resource classes. Visit `/admin` to see the panel.

## Define a resource

Place resource classes in `app/Rodeo/`. Each class extends `RodeoPHP\Resource` and implements `form()` and `table()`.

```php
<?php

declare(strict_types=1);

namespace App\Rodeo;

use RodeoPHP\Fields\Select;
use RodeoPHP\Fields\Text;
use RodeoPHP\Fields\Textarea;
use RodeoPHP\Fields\Toggle;
use RodeoPHP\Forms\Form;
use RodeoPHP\Resource;
use RodeoPHP\Tables\Columns\TextColumn;
use RodeoPHP\Tables\Table;
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

Resources are discovered automatically by scanning `app/Rodeo/` at boot — no manual registration needed.

## Configuration

`rodeo:install` publishes `config/rodeo.php`. Available keys:

| Key | Default | Description |
|---|---|---|
| `path` | `'admin'` | URL prefix for the panel (e.g. `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Middleware stack applied to all panel routes. |
| `resources.path` | `app_path('Rodeo')` | Filesystem path scanned for resource classes. |
| `resources.namespace` | `'App\\Rodeo'` | PHP namespace corresponding to `resources.path`. |
| `per_page` | `25` | Default rows per page on index tables. |
| `brand.title` | `'RodeoPHP'` | Browser tab title. |
| `brand.name` | `'RodeoPHP'` | Panel sidebar name. |
| `brand.accent` | `'#d9501f'` | Accent colour (buttons, active states). |

## Commands

| Command | Description |
|---|---|
| `rodeo:install` | Publish config, publish panel assets, create `app/Rodeo/`. Offers to add `rodeo:upgrade` to `composer post-update-cmd` so assets stay fresh. |
| `rodeo:upgrade` | Re-publish panel assets. Run after every package update. |
| `rodeo:resource NameResource --model=Name` | Scaffold a new resource class. The `--model` option is optional; it is inferred from the resource name when omitted. |

## Local development

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

The `workbench/` directory contains a minimal Laravel application for manual testing. Spin it up with:

```bash
vendor/bin/testbench serve
```

Then visit `http://localhost:8000/admin`. The workbench registers `HorseResource` and seeds a handful of horses so the index table renders out of the box.

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
