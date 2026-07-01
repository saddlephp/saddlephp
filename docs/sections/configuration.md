Saddle ships with a single config file published by `saddle:install`. All keys have sensible defaults so you can ignore this file until you need to change something.

### Config keys

`saddle:install` publishes `config/saddle.php`. Available keys:

| Key | Default | Description |
|---|---|---|
| `path` | `'admin'` | URL prefix for the panel. The value `'admin'` makes the panel available at `/admin`. |
| `middleware` | `['web', 'auth']` | Middleware stack applied to all panel routes. Add your own guards or throttle rules here. |
| `resources.path` | `app_path('Saddle')` | Filesystem path scanned for resource classes at boot. |
| `resources.namespace` | `'App\\Saddle'` | PHP namespace corresponding to `resources.path`. |
| `per_page` | `25` | Default number of rows shown on index tables. |
| `brand.name` | `'Saddle'` | Panel name shown in the sidebar and browser tab. |
| `brand.accent` | `'#d9501f'` | Accent colour used for buttons and active states. |
| `uploads.disk` | `'public'` | Default filesystem disk used by `FileUpload` fields when no per-field `disk()` is set. |
| `uploads.directory` | `'saddle'` | Default upload directory within the disk when no per-field `directory()` is set. |

### Artisan commands

| Command | Description |
|---|---|
| `saddle:install` | Publishes the config file, publishes panel assets to `public/vendor/saddle/`, and creates `app/Saddle/`. Offers to add `saddle:upgrade` to `composer.json`'s `post-update-cmd`. |
| `saddle:upgrade` | Re-publishes the panel assets. Run after every package update. If you accepted the `post-update-cmd` prompt, Composer runs this for you automatically. |
| `saddle:resource NameResource --model=Name` | Scaffolds a new resource class in `app/Saddle/`. The `--model` option is optional; when omitted the model name is inferred from the resource class name. |
