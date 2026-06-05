Layout containers group fields for presentation. They are declared inside `form()` alongside regular fields and nest freely inside one another.

### Section

A labeled card that groups related fields visually.

```php
Section::make('Identity')->description('Who this horse is on the ranch.')->schema([
    Text::make('name')->required(),
    Select::make('breed')->options([...]),
]),
```

`make(string $label)` sets the heading. `description(string)` adds an optional subtitle line. `schema([...])` accepts fields and other containers.

### Grid

Arranges its children in a CSS grid.

```php
Grid::make(2)->schema([
    Text::make('name')->required()->rules('max:120'),
    Select::make('breed')->options([...]),
]),
```

`Grid::make(int $columns)` sets the column count (default `2`). Each child field uses `columnSpan(int)` to span multiple columns within the grid:

```php
Grid::make(3)->schema([
    Text::make('name')->columnSpan(2),
    Select::make('breed'),
]),
```

### Tabs and Tab

`Tabs` wraps one or more `Tab` panes in a tabbed interface.

```php
Tabs::make([
    Tab::make('Care')->schema([
        Markdown::make('notes'),
        DateTime::make('last_vet_visit'),
    ]),
    Tab::make('Assignment')->schema([
        BelongsTo::make('rider')->searchable(),
        Toggle::make('is_saddled'),
    ]),
]),
```

`Tab::make(string $label)` creates a single pane. Each pane takes its own `schema([...])` of fields and containers.

**Validation error indicator.** When any field inside a tab fails validation, the tab label shows an error indicator so users can locate the problem without switching to every pane manually.

### Flat schemas

Passing a plain list of fields to `$form->schema([...])` with no containers produces a single-column stacked layout and is fully supported. Layout containers are purely opt-in. Validation and authorization treat fields identically regardless of whether they live inside a container or at the top level.
