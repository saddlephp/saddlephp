Saddle ships a testing trait so you can assert your panel is wired the way you expect, without driving the browser.

### Using the trait

Add `InteractsWithSaddle` to a test class (PHPUnit or Pest):

```php
use SaddlePHP\Testing\InteractsWithSaddle;

uses(InteractsWithSaddle::class); // Pest

it('exposes the horse panel', function () {
    $this->assertResourceRegistered('horses');
    $this->assertResourceHasField(HorseResource::class, 'name');
    $this->assertResourceHasColumn(HorseResource::class, 'breed');
});
```

### Helpers

| Method | Description |
|---|---|
| `assertResourceRegistered(string $uriKey)` | The panel has a resource for that URI key. |
| `assertResourceHasField(string $resource, string $name)` | The resource's form contains the field (searched through sections, grids, and tabs). |
| `assertResourceMissingField(string $resource, string $name)` | The inverse, for negative tests. |
| `assertResourceHasColumn(string $resource, string $name)` | The resource's table contains the column. |
| `saddleForm(string $resource): array` | The serialized form schema, for custom assertions. |
| `saddleTable(string $resource): array` | The serialized column list, for custom assertions. |

These introspect your resource definitions directly, so they are fast and need no HTTP round-trip. For end-to-end checks, drive the panel's routes with Laravel's HTTP and Inertia testing helpers as usual.

### Serializing a form or table by hand

If you build a `Form` or `Table` in a test rather than going through a resource,
serialize it with `toArray()` — the same name the leaves use:

```php
$fields = Form::make()->schema([Text::make('name')])->toArray($record);
$columns = Table::make()->columns([TextColumn::make('name')])->toArray($request);
```

`toInertia()` is the original name and still works; since 1.5.0 `toArray()` is an
alias for it, so `Field`, `Column`, `Form` and `Table` all answer to the same
method. Both go through the same `canSee()` gates — the alias is not a way to
serialize a hidden field or column.
