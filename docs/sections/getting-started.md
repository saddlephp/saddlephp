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
2. Publishes the compiled panel assets to `public/vendor/saddlephp/`.
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

### Generating a resource

```bash
php artisan saddle:resource PostResource --model=Post
```

The `--model` option is optional. When omitted, the model name is inferred from the resource name (`PostResource` infers `Post`). The generated class is placed in `app/Saddle/PostResource.php` and registered automatically on the next request.
