Fields live inside `form()` and define what the create and edit forms render. Every field is constructed with a static `make(string $name)` call, then chained with modifiers.

### Text

A single-line text input.

```php
Text::make('name')->required()->placeholder('Full name')->rules('max:120'),
```

Modifiers specific to `Text`: `type(string)` changes the HTML input type (e.g. `'email'`, `'number'`). The default type is `'text'`, which also adds a `string` validation rule. `'email'` adds an `email` rule; `'number'` adds `numeric`.

### Textarea

A multi-line text input.

```php
Textarea::make('notes')->rows(3),
```

`rows(int)` controls the visible height. Default is 4 rows.

### Select

A fixed-options dropdown. Pass an associative array of `value => label` pairs.

```php
Select::make('breed')->options([
    'quarter' => 'Quarter Horse',
    'mustang'  => 'Mustang',
    'appaloosa' => 'Appaloosa',
]),
```

You may also pass a backed enum class-string instead of an array. Submitted values are validated against the declared options, so undeclared values are rejected.

### Toggle

A boolean on/off switch. Defaults to `false`. Toggles cannot be `required()` (calling it is a no-op by design) because an unchecked toggle is a valid `false` submission, not a missing value.

```php
Toggle::make('is_saddled'),
```

On save the value is cast to a real `bool` before being assigned to the model.

### Number

A numeric input.

```php
Number::make('age')->integer()->min(0)->max(50),
Number::make('weight')->min(0.1)->step(0.1)->max(9999),
```

| Modifier | Effect |
|---|---|
| `integer()` | Adds an `integer` validation rule (default is `numeric`). |
| `min(int\|float)` | Sets the minimum allowed value. |
| `max(int\|float)` | Sets the maximum allowed value. |
| `step(int\|float)` | Controls the increment step shown in the browser input. |

### Date

A date input. Values are resolved as `Y-m-d` strings (any `DateTimeInterface` attribute is formatted automatically).

```php
Date::make('foaled_on'),
```

### DateTime

A date-and-time input (`datetime-local`). On save the value is passed directly to the model; use a `datetime` cast on the attribute so Eloquent stores it correctly. When resolving for the edit form, a `DateTimeInterface` value is formatted to `Y-m-dTH:i` automatically.

```php
DateTime::make('last_vet_visit'),
```

### Markdown

A textarea with a formatting toolbar. The stored value is a plain string bounded to 65 535 characters (matches a MySQL `TEXT` column).

```php
Markdown::make('notes'),
```

All modifiers inherited from `Textarea` (`rows()`) and the common base (`required()`, `rules()`, `placeholder()`, `helper()`, `canSee()`, `label()`) apply as usual.

### FileUpload

A multipart file upload. The stored value is the file path returned by `Storage::put`.

```php
FileUpload::make('photo')->image()->directory('horses')->maxSize(4096),
FileUpload::make('attachment')->acceptedTypes(['pdf', 'docx'])->maxSize(10240),
```

| Modifier | Effect |
|---|---|
| `disk(string)` | Storage disk to write to. Defaults to `saddle.uploads.disk` from config. |
| `directory(string)` | Sub-directory within the disk. Defaults to `saddle.uploads.directory` from config. |
| `image()` | Restricts the upload to image files (adds the `image` validation rule and sets the browser `accept` attribute to `image/*`). |
| `acceptedTypes(array)` | List of accepted MIME extensions, e.g. `['pdf', 'docx']`. Adds a `mimes:` validation rule and sets the browser `accept` attribute accordingly. |
| `maxSize(int $kilobytes)` | Maximum file size in kilobytes (maps to Laravel's `max` file rule). |

**Edit-form behavior.** When a file is already stored, leaving the upload input untouched keeps the existing file. Clearing the input stores `null`. Picking a new file replaces the stored path. Replaced or cleared files are not deleted from disk automatically.

### BelongsTo

A relation select for `BelongsTo` relationships. The argument is the Eloquent relation method name on the model.

```php
BelongsTo::make('rider'),
BelongsTo::make('rider')->searchable(),
BelongsTo::make('rider')->limit(50)->modifyOptionsQuery(
    fn ($query) => $query->where('stable_id', $this->stable_id)
),
```

The field reads `$model->rider()`, confirms it is a `BelongsTo` relation, and submits the foreign key on save.

**Label resolution order.** Option labels are resolved in this order:

1. `titleAttribute('attribute')` if you have set one on the field.
2. The related model's registered resource `$title`.
3. The related model's primary key.

Set `titleAttribute('name')` explicitly if the related model has no registered resource, or your options list will show raw IDs.

**Limit.** Options are capped at 100 by default. Override with `limit(int)`.

**Async picker.** `searchable()` switches from a static list to an async picker that queries the related table as you type. On the edit form, only the currently saved value is embedded; the full list is never loaded up front.

**Scoping options.** `modifyOptionsQuery(fn ($query) => ...)` scopes the option list for tenancy or visibility. It applies to both the static list and async search results. A record's saved foreign key always renders its label even when the related row falls outside the scope.

### CustomField

Renders a custom element supplied by a plugin. See the Plugins section for the full contract.

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
```

`tag(string)` is required. It must match the custom element name registered by your plugin script.

### Common modifiers

All fields share these modifiers from the base `Field` class:

| Modifier | Effect |
|---|---|
| `required()` | Adds `required` validation; removes `nullable`. |
| `rules(string\|array)` | Appends extra validation rules (accepts anything Laravel's validator accepts). |
| `default(mixed)` | Sets the value shown on the create form when no record exists. |
| `placeholder(string)` | Hint text shown inside the input when empty. |
| `helper(string)` | Short help text rendered below the field. |
| `canSee(Closure)` | Gates field visibility per request. See the Authorization section. |
| `label(string)` | Overrides the auto-generated label (headline-cased attribute name by default). |
