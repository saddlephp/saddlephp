Dashboard widgets turn the panel's landing page into a live overview. A widget is a small PHP class that computes its data per request; Saddle discovers them automatically and renders them at the top of the dashboard.

### Stat widgets

A `StatWidget` shows a number with a label, an optional description, and an optional inline bar chart, exactly like the cards on the marketing banner.

```php
use Illuminate\Http\Request;
use SaddlePHP\Widgets\StatWidget;
use App\Models\Horse;

class HorseCountWidget extends StatWidget
{
    public static int $sort = 0;

    public function label(): string
    {
        return 'Horses';
    }

    public function value(Request $request): int
    {
        return Horse::count();
    }

    public function description(Request $request): ?string
    {
        return Horse::where('is_saddled', true)->count().' saddled';
    }

    // Optional inline mini bar chart on the card.
    public function chart(Request $request): array
    {
        return [3, 5, 2, 8, 6, 9];
    }
}
```

### Chart widgets

A `ChartWidget` renders a full-width bar chart from an ordered `label => value` map.

```php
use Illuminate\Http\Request;
use SaddlePHP\Widgets\ChartWidget;
use App\Models\Horse;

class HorsesByBreedWidget extends ChartWidget
{
    public static int $sort = 1;

    public function heading(): string
    {
        return 'Horses by breed';
    }

    public function data(Request $request): array
    {
        return Horse::query()
            ->selectRaw('breed, count(*) as c')
            ->groupBy('breed')
            ->pluck('c', 'breed')
            ->all();
    }
}
```

### Discovery, ordering, and visibility

Place widget classes in `app/Saddle/Widgets/` and Saddle discovers them automatically, the same way it discovers resources. Widgets render in ascending `$sort` order. To register widgets explicitly instead of by discovery, call `Saddle::registerWidgets([...])` from a service provider. The discovery path is configurable via `config('saddle.widgets')`.

### Authorization

Declare the resource a widget summarises, and it is gated by that resource's `viewAny` policy:

```php
class HorseCountWidget extends StatWidget
{
    public static ?string $resource = HorseResource::class;
}
```

An aggregate discloses more than it looks like it does. A bare count on a shared dashboard tells a competitor tenant your customer count, your order volume, and — watched over time — your growth rate.

A widget that declares **no** resource cannot be authorized by anything, so it is hidden. You have three ways to show one deliberately:

- point it at a resource with `$resource` (recommended),
- override `canSee(Request $request): bool` to return `true` for a genuinely public tile,
- or set `saddle.authorization.require_widget_resource` to `false` to restore the old fail-open behaviour panel-wide.

`canSee()` still overrides everything, so any existing gate you have written keeps working.

**Saddle tells you when it hides one.** A missing tile with no explanation is a
confusing absence rather than a one-line fix, so a widget hidden for this reason
throws a `LogicException` naming the class in the `local` environment, and logs a
warning everywhere else. The split is deliberate: a live panel losing a tile must
not become a live panel losing its dashboard.

There is nothing to silence. Any of the three fixes above makes the widget
authorizable, and an authorizable widget says nothing — including one whose
policy simply denies the current user, which is a decision rather than a
misconfiguration.

### Tenancy

Widgets query models directly, so `Resource::query()`'s tenant scoping never applies to them. Wrap the query in `scopeToTenant()` and it is confined to the bound tenant using the relation declared on the widget's `$resource`:

```php
public function value(Request $request): int
{
    return $this->scopeToTenant(Horse::query())->count();
}
```

It is a no-op when tenancy is off, when no tenant is bound, or when the declared resource is global by design, so it is safe to leave in place either way. Pass a relation name explicitly (`scopeToTenant($query, 'ranch')`) when the widget's model is scoped differently from its resource.

Forgetting this is quiet rather than loud: the tile simply reports the **global** figure to every tenant.

One widget that throws while building is skipped (and reported) rather than breaking the whole dashboard.
