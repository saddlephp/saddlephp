<p align="center">
  <strong>RodeoPHP</strong><br>
  <em>Saddle up cowboy, there's a new CMF in town.</em>
</p>

---

**RodeoPHP** is the open-source admin & content framework for Laravel, built the modern way for **Inertia and
Vue**. Round up your Eloquent models into polished resource panels, with form & table builders, roles & access,
plugins, and multi-tenancy.

> **Status: early frontier (`v0.1.0-dev`).** This repository is being staged. The marketing site lives at
> **[rodeophp.com](https://rodeophp.com)** ([RodeoPHP/rodeophp.com](https://github.com/RodeoPHP/rodeophp.com)).

## Installation

```bash
composer require rodeophp/rodeophp
```

The service provider is auto-discovered. Publish the config if you want to customise it:

```bash
php artisan vendor:publish --tag=rodeo-config
```

## A taste

```php
use RodeoPHP\Facades\Rodeo;

Rodeo::greeting(); // "Saddle up, cowboy. There's a new CMF in town."
Rodeo::version(); // "0.1.0-dev"
```

## Roadmap

- [ ] Resource panels (CRUD from an Eloquent model)
- [ ] Form builder
- [ ] Table builder
- [ ] Roles & access (policy-driven)
- [ ] Plugins
- [ ] Multi-tenancy

## Stack

Built for **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## License

MIT.
