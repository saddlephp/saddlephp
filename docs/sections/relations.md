A relation manager renders a table of a record's `HasMany` children on its view page, with full create, edit, and delete, all scoped to the parent. A ranch manages its horses; an author manages their posts. The related records never leave the parent's relationship, so a child from another parent (or another tenant) can never be read or written through it.

### Declaring a relation manager

Create a class that extends `RelationManager` and names the parent's `HasMany` method. It carries its own `table()` and `form()`, exactly like a resource.

```php
use SaddlePHP\Fields\Text;
use SaddlePHP\Fields\Toggle;
use SaddlePHP\Forms\Form;
use SaddlePHP\RelationManager;
use SaddlePHP\Tables\Columns\BooleanColumn;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;

class HorsesRelationManager extends RelationManager
{
    protected static string $relationship = 'horses'; // Ranch::horses() — a HasMany

    public static ?string $title = 'name';

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
            BooleanColumn::make('is_saddled'),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name')->required(),
            Toggle::make('is_saddled'),
        ]);
    }
}
```

Generate one with the maker command:

```bash
php artisan saddle:relation HorsesRelationManager --relationship=horses
```

When you omit `--relationship`, it is guessed from the class name (`HorsesRelationManager` → `horses`).

### Registering it on a resource

Return your relation managers from `relations()` on the parent resource. They appear on the parent's view page.

```php
class RanchResource extends Resource
{
    public static string $model = Ranch::class;

    public static function relations(): array
    {
        return [HorsesRelationManager::class];
    }

    // form(), table() as usual
}
```

### Authorization

Each relation manager authorizes against the **related model's** policy, not the parent's. Listing checks `viewAny`, the New button and store check `create`, and per-row Edit and Delete check `update` and `delete`. With no policy registered for the related model, every ability is allowed (the same no-policy default as resources); turn on `saddle.authorization.require_policy` to make a missing policy fail closed.

### Scoping guarantees

Every read and write flows through `$parent->{relationship}()`. New records are created through the relationship, so the foreign key is stamped by the framework and can never be smuggled in through the payload. Resolving a related record for edit, update, or delete is constrained to the parent, so an ID belonging to a different parent returns 404 before any handler runs. When the parent resource is tenant-scoped, the parent itself resolves through the tenant query, so its children are isolated per tenant automatically.

### Scope (v0.9)

Relation managers cover `HasMany` relationships and render on the view page. Create and edit happen in a slide-over modal; delete asks for confirmation. The table shows the first page of related records; paging through additional pages of related rows lands in a later release.
