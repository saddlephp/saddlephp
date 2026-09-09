Saddle ships with a single config file published by `saddle:install`. All keys have sensible defaults so you can ignore this file until you need to change something.

### Config keys

`saddle:install` publishes `config/saddle.php`. Available keys:

| Key | Default | Description |
|---|---|---|
| `path` | `'admin'` | URL prefix for the panel. The value `'admin'` makes the panel available at `/admin`. |
| `middleware` | `['web', 'auth']` | Middleware stack applied to all panel routes. Add your own guards or throttle rules here. |
| `resources.path` | `app_path('Saddle')` | Filesystem path scanned for resource classes at boot. |
| `resources.namespace` | `'App\\Saddle'` | PHP namespace corresponding to `resources.path`. |
| `resources.discovery` | `true` | Scan `resources.path` at boot. Discovered resources are merged with anything passed to `Saddle::register()`. Set to `false` to skip scanning and curate the list by hand. |
| `per_page` | `25` | Default number of rows shown on index tables. |
| `global_search.per_resource` | `5` | Maximum results returned per resource by global search. |
| `widgets.path` | `app_path('Saddle/Widgets')` | Filesystem path scanned for widget classes. |
| `widgets.namespace` | `'App\\Saddle\\Widgets'` | PHP namespace corresponding to `widgets.path`. |
| `widgets.discovery` | `true` | As `resources.discovery`, for widgets. |
| `brand.name` | `'Saddle'` | Panel name shown in the sidebar and browser tab. |
| `brand.accent` | `'#d9501f'` | Accent colour used for buttons and active states. |
| `brand.greeting` | `null` | Dashboard headline. May contain `:name`. `null` keeps Saddle's own wording. |
| `brand.subgreeting` | `null` | The line under the headline. `null` keeps Saddle's own wording. |
| `authorization.require_policy` | `true` | Deny when a model has no registered policy. Setting this to `false` makes the panel fail open. |
| `tenancy.model` | `null` | Tenant model class. Tenancy is off entirely while this is `null`. |
| `tenancy.foreign_key` | `'tenant_id'` | Column used to scope tenant-owned records. |
| `tenancy.gate` | `null` | Optional closure name gating tenant access. |
| `tenancy.registration` | `null` | Handler enabling self-service tenant registration. |
| `uploads.disk` | `'public'` | Default filesystem disk used by `FileUpload` fields when no per-field `disk()` is set. Note that `public` means any panel user can host content on your own origin; see below. |
| `uploads.directory` | `'saddle'` | Default upload directory within the disk when no per-field `directory()` is set. |
| `uploads.max_size` | `10240` | Ceiling in kilobytes for any `FileUpload` that does not set its own `maxSize()`. |
| `uploads.allowed_extensions` | `[]` | Accepted types for a `FileUpload` with no `image()` or `acceptedTypes()` of its own. Empty uses the framework default set. |
| `import.max_rows` | `5000` | Rows accepted by a CSV import before the request is rejected with a 422. |
| `export.max_rows` | `50000` | Rows written by a CSV export. `0` removes the cap. |

### Dashboard greeting

The dashboard opens with two lines. Both are configurable, and both default to
`null`, which keeps the wording Saddle has always shipped ("Howdy, Matthew." /
"Pick a resource and get ridin'.").

```php
'brand' => [
    'greeting' => 'Welcome, :name.',
    'subgreeting' => 'Spend, by channel, for the last 30 days.',
],
```

`:name` is replaced with the signed-in user's name. With nobody signed in the
placeholder is removed **along with the separator in front of it**, so
`'Welcome, :name.'` reads `Welcome.` rather than `Welcome, .`.

One rough edge worth knowing: a separator *after* a leading placeholder is kept,
so `':name — spend'` degrades to `'— spend'`. Removing it would mangle
`'Howdy :name,'`, which is the far commoner shape. Put the placeholder last if
that matters for your wording.

### Upload safety

`acceptedTypes()` and `image()` are security controls, not UX hints.

Laravel names a stored file from its *detected content type*, not the name the
uploader supplied. A file called `notes.txt` whose contents are HTML is stored
as `<random>.html`, and on the default `public` disk it is then served from your
application's own origin. An admin who opens the record and clicks the
attachment is running attacker-controlled script with their own session.

Since 1.3.0 a `FileUpload` with no constraint of its own falls back to
`uploads.allowed_extensions`, whose default set excludes `html`, `svg`, `xml`,
`js` and the `php` family. Declaring `acceptedTypes()` per field is still
better, and storing on a private disk with signed URLs is better again.

### Artisan commands

| Command | Description |
|---|---|
| `saddle:install` | Publishes the config file, publishes panel assets to `public/vendor/saddle/`, and creates `app/Saddle/`. Offers to add `saddle:upgrade` to `composer.json`'s `post-update-cmd`. |
| `saddle:upgrade` | Re-publishes the panel assets. Run after every package update. If you accepted the `post-update-cmd` prompt, Composer runs this for you automatically. |
| `saddle:resource NameResource --model=Name` | Scaffolds a new resource class in `app/Saddle/`. The `--model` option is optional; when omitted the model name is inferred from the resource class name. |
