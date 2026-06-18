A resource is a PHP class that maps one Eloquent model to an admin panel section. It declares the form fields used to create and edit records, and the table columns and filters used to list them.

### Anatomy of a resource

Place resource classes in `app/Saddle/`. Each class extends `SaddlePHP\Resource` and implements two static methods: `form()` and `table()`.

```php
<?php

declare(strict_types=1);

namespace App\Saddle;

use App\Models\Horse;
use Illuminate\Http\Request;
use SaddlePHP\Fields\BelongsTo;
use SaddlePHP\Fields\Date;
use SaddlePHP\Fields\Number;
use SaddlePHP\Fields\Select;
use SaddlePHP\Fields\Text;
use SaddlePHP\Fields\Textarea;
use SaddlePHP\Fields\Toggle;
use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\BadgeColumn;
use SaddlePHP\Tables\Columns\BooleanColumn;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Filters\BooleanFilter;
use SaddlePHP\Tables\Filters\SelectFilter;
use SaddlePHP\Tables\Table;

class HorseResource extends Resource
{
    public static string $model = Horse::class;

    public static ?string $title = 'name';

    public static ?string $icon = 'collection';

    public static array $with = ['rider'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name')->required()->rules('max:120'),
            Select::make('breed')->options([
                'quarter' => 'Quarter Horse',
                'mustang' => 'Mustang',
                'appaloosa' => 'Appaloosa',
            ]),
            Textarea::make('notes')->rows(3)
                ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
            Toggle::make('is_saddled'),
            BelongsTo::make('rider')->searchable(),
            Number::make('age')->integer()->min(0)->max(50),
            Date::make('foaled_on'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->sortable()->searchable(),
            BadgeColumn::make('breed')->colors([
                'quarter' => 'accent',
                'mustang' => 'ink',
                'appaloosa' => 'muted',
            ]),
            BooleanColumn::make('is_saddled'),
            TextColumn::make('rider.name')->label('Rider'),
            TextColumn::make('created_at')->date('M j, Y')->sortable(),
        ])->filters([
            SelectFilter::make('breed')->options([
                'quarter' => 'Quarter Horse',
                'mustang' => 'Mustang',
                'appaloosa' => 'Appaloosa',
            ]),
            BooleanFilter::make('is_saddled'),
        ]);
    }
}
```

### Static properties

| Property | Type | Description |
|---|---|---|
| `$model` | `string` (class-string) | Required. The Eloquent model class this resource manages. |
| `$title` | `string\|null` | The model attribute used as the record title. Falls back to the primary key when `null`. |
| `$icon` | `string\|null` | Icon name shown next to the resource in the sidebar. |
| `$group` | `string\|null` | Sidebar group heading. Resources with the same group value are visually grouped together. |
| `$with` | `string[]` | Relations eager-loaded on every index query. Required for relation columns (e.g. `rider.name`). |

### Auto-discovery

Saddle scans `app/Saddle/` at boot and registers every class it finds that extends `SaddlePHP\Resource`. No manual registration is needed for application resources. The scan path and namespace are configurable via `config/saddle.php` if your project uses a non-standard layout.

### URI key and label derivation

The panel derives the URL key and navigation label automatically from the class name:

- `HorseResource` produces uriKey `horses` and label `Horses`.
- `TrailRideResource` produces uriKey `trail-rides` and label `Trail Rides`.

The class base name (everything before the `Resource` suffix) is pluralised and converted to kebab-case for the URI, and to headline case for the label.

### Record titles

`recordTitle()` returns the string the panel uses to identify a record in breadcrumbs and relation pickers. It reads the attribute named by `$title`. When `$title` is `null`, it falls back to the primary key.

```php
// In a resource:
public static ?string $title = 'name';

// The panel calls:
$resource::recordTitle($record); // returns $record->name as a string
```

### View page

Every record has a read-only view page, reachable from the **View** link on each index row. It renders the same form fields the edit page uses, in display mode: select and relation fields show their label, toggles show a checkmark, dates are formatted, files become links. Field layout (sections, grids, tabs) is preserved. Fields hidden by `canSee` are omitted here too.

The view page is gated by the `view` policy ability, and carries an **Edit** button when the user may update the record. It is also the host for relation managers (see the relations section).
