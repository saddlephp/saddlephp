Global search lives at the top of the panel sidebar. It searches every resource at once and links each result straight to its view page, so you can jump to any record without first navigating to its list.

### What gets searched

A resource is included in global search when its table declares `searchable()` columns:

```php
public static function table(Table $table): Table
{
    return $table->columns([
        TextColumn::make('name')->searchable(),
        TextColumn::make('email')->searchable(),
    ]);
}
```

The same columns power the index search box and global search. A resource with no searchable columns is simply skipped.

### Opting out

A resource is searchable by default. Set `$globalSearch` to `false` to keep it out of global search while leaving its index search intact:

```php
class AuditLogResource extends Resource
{
    public static bool $globalSearch = false;
    // ...
}
```

### Authorization and tenancy

Global search only includes resources the current user may `viewAny`, and every query runs through the resource's scoped base query. Policy gates and multi-tenancy apply automatically, so a user never sees a record they could not reach through the resource itself.

### Result limits

Each resource contributes at most `saddle.global_search.per_resource` results (default `5`). Raise or lower it in `config/saddle.php`:

```php
'global_search' => [
    'per_resource' => 5,
],
```

One resource that errors while searching is skipped (and reported) rather than breaking the whole search, the same way a broken resource never takes down the sidebar.
