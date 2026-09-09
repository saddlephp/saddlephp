The `table()` method on a resource configures the index listing: which columns to show, how they can be sorted or searched, and which filters are available. Columns are declared inside `Table::columns([...])`.

### TextColumn

Renders the raw attribute value as text.

```php
TextColumn::make('name')->sortable()->searchable(),
TextColumn::make('created_at')->date('M j, Y')->sortable(),
```

| Modifier | Effect |
|---|---|
| `sortable()` | Allows the column to be sorted by clicking its header. |
| `searchable()` | Includes this column in the panel's full-text search. |
| `label(string)` | Overrides the auto-generated column heading. |
| `date(string $format)` | Formats a `DateTimeInterface` attribute with the given format string. The default format when `date()` is called without an argument is `Y-m-d H:i`. |
| `formatUsing(Closure)` | Transforms the resolved value before it is rendered. Available on every column type — see [Formatting a cell](#formatting-a-cell). |

### BadgeColumn

Renders a pill badge. Use `colors()` to map option values to color tokens.

```php
BadgeColumn::make('breed')->colors([
    'quarter'   => 'accent',
    'mustang'   => 'ink',
    'appaloosa' => 'muted',
]),
```

Available color tokens: `accent`, `ink`, `muted`. Values not present in the map are rendered without a color token.

### BooleanColumn

Renders a check mark for truthy values and a dash for falsy ones.

```php
BooleanColumn::make('is_saddled'),
```

The resolved value is cast to a real `bool` before being passed to the frontend.

### CustomColumn

Renders a custom element supplied by a plugin. The column sets `value` and `column` DOM properties on the element (read-only; no input event expected). See the Plugins section for details.

```php
CustomColumn::make('mood')->tag('mood-cell'),
```

### Formatting a cell

`formatUsing()` sits between resolving a cell's value and rendering it. It is
available on every column type.

```php
TextColumn::make('spend')
    ->sortable()
    ->formatUsing(fn (mixed $value) => $value === null ? '—' : Number::currency($value)),

TextColumn::make('name')
    ->formatUsing(fn (mixed $value, Model $record) => "{$value} ({$record->breed})"),
```

The callback receives the value **and the record**, so it can format from a
related field without a second query.

The reason this belongs on the column rather than on a model accessor is
sorting. An accessor cannot be `->sortable()` — sorting happens in the database
and the accessor does not exist there. `formatUsing()` runs *after* the value is
resolved, so `sortable()` and `searchable()` still refer to the real column
while the cell renders whatever you want. Without it, a panel that renders a
null as an em dash in a `StatWidget` (whose `value()` returns a string) has no
way to do the same in the table underneath.

Two things worth knowing:

- It applies wherever a cell's value is resolved, **including the CSV export**.
  An export of a formatted column exports the formatted text.
- On a `TextColumn` that also calls `date()`, the callback sees the raw value
  (a `DateTimeInterface`), and `date()` then formats only what is still a date
  afterwards. Format the date inside the callback if you want both.

### Relation columns and eager loading

Dotted names read through a loaded relation:

```php
TextColumn::make('rider.name')->label('Rider'),
```

For this to work, the relation must already be loaded. Declare `$with` on the resource to eager-load it on every index query:

```php
public static array $with = ['rider'];
```

Relation columns are not sortable or searchable in the current release.

### Search behavior

The panel applies a `LIKE %search%` query across all columns marked `searchable()`, combined with `orWhere` clauses. The search term arrives via the `search` query string parameter.

### Sort behavior

Clicking a sortable column header toggles between ascending and descending order. The active sort and direction are reflected in the `sort` and `direction` query string parameters. When no valid sort is requested, records default to descending primary key order.
