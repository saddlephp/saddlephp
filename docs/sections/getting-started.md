Saddle is the open-source admin panel framework for Laravel, built on Inertia and Vue 3. Three commands are all you need to go from a fresh Laravel app to a working admin panel.

### Requirements

- **Laravel 13+** and **PHP 8.4+**
- **Inertia 2**, **Vue 3**, and **Tailwind CSS 4** (the panel bundle brings these; your app just needs a working Vite setup)

### Install

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

The service provider is auto-discovered by Laravel, so no manual wiring is needed.

### What `saddle:install` publishes

Running `saddle:install` does three things:

1. Publishes `config/saddle.php` with sensible defaults.
2. Publishes the compiled panel assets to `public/vendor/saddle/`.
3. Creates the `app/Saddle/` directory where your resource classes live.

It also offers to add `saddle:upgrade` to `composer.json`'s `post-update-cmd` scripts so assets stay fresh after every package update. Accept the prompt to set this up automatically.

### Visiting the panel

Start your application and open `/admin` in a browser. The panel sits behind the `auth` middleware by default, so you must be logged in. The `path` config key controls the URL prefix if you need something other than `admin`.

### Staying up to date

After each `composer update` that bumps the package, re-publish the panel assets:

```bash
php artisan saddle:upgrade
```

If you accepted the `post-update-cmd` prompt during install, Composer runs this for you automatically.

Since 1.5.0 the bundle also publishes under Laravel's own `laravel-assets` tag,
which a fresh Laravel application's `composer.json` already calls on
`post-update-cmd`. On such an application the assets are refreshed by
`composer update` with no Saddle-specific setup at all.

**This is not optional maintenance.** The compiled Vue bundle is a published
file under `public/`; upgrading the package replaces the PHP and leaves that
file exactly where it was. A panel in that state passes every server-side check
-- the version constant, the new classes, the new methods -- while rendering the
*previous* frontend, so the only symptom is that the feature you upgraded for
appears to be missing. If you pin a specific tag, or deploy from a build
artifact, make sure republishing the assets is part of that pipeline.

### Generating a resource

```bash
php artisan saddle:resource PostResource --model=Post
```

The `--model` option is optional. When omitted, the model name is inferred from the resource name (`PostResource` infers `Post`). The generated class is placed in `app/Saddle/PostResource.php` and registered automatically on the next request.
