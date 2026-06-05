Actions let you run operations on individual records or a selection of records directly from the index table. Row actions appear as buttons on each row; bulk actions appear in a toolbar when one or more rows are selected.

### Declaring actions

Override `actions()` and `bulkActions()` on your resource class. Each method returns an array of action objects.

```php
use Illuminate\Database\Eloquent\Collection;
use SaddlePHP\Actions\Action;
use SaddlePHP\Actions\BulkAction;

public static function actions(): array
{
    return [
        Action::make('unsaddle')
            ->handle(fn (Horse $horse) => $horse->update(['is_saddled' => false]))
            ->requiresConfirmation('Unsaddle this horse?')
            ->color('accent'),
    ];
}

public static function bulkActions(): array
{
    return [
        BulkAction::make('saddle-up')
            ->label('Saddle up')
            ->handle(fn (Collection $horses) => $horses->each->update(['is_saddled' => true])),
        BulkAction::delete(),
    ];
}
```

### Fluents

| Method | Description |
|---|---|
| `label(string)` | Display label for the button. When omitted, the action name is converted to title case automatically. |
| `color(string)` | Button color token: `accent`, `ink`, or `muted`. Defaults to `ink`. |
| `requiresConfirmation(?string)` | Show a confirmation dialog before the action runs. Pass a custom message string or omit to use the default prompt. |
| `authorize(string)` | Name a policy ability to check per record before the handler runs. |
| `successMessage(string)` | Flash message shown to the user after successful execution. Defaults to `Done.`. |

### How execution works

Actions post to an endpoint that sits behind the same middleware stack as the rest of the panel. Records resolve through the same scoped base query used by index, edit, and delete, so tenancy scopes and per-resource query constraints apply automatically. Cross-tenant or otherwise out-of-scope record IDs return 404 before any handler runs.

Every action invocation wraps the handler in a database transaction. For bulk actions this means the operation is all-or-nothing: if the handler throws, the transaction rolls back and no record is partially modified. Bulk requests are capped at 100 records per call; submitting more than 100 IDs is rejected at the validation layer.

When `authorize('ability')` is set, the panel calls your policy's named ability for each record in scope before running the handler. For bulk actions, every record in the selection must pass; a single failure aborts the entire operation with 403. Declare `authorize()` on any action that modifies or removes data.

### BulkAction::delete()

`BulkAction::delete()` is a pre-built preset for the common case of bulk deletion. It ships with:

- Name `delete`, label `Delete`, color `accent`
- Confirmation prompt `Delete the selected records?`
- `authorize('delete')` wired automatically
- A handler that calls `delete()` on every record in the collection

Add it to `bulkActions()` without any further configuration:

```php
public static function bulkActions(): array
{
    return [
        BulkAction::delete(),
    ];
}
```
