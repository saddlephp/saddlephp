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

Place widget classes in `app/Saddle/Widgets/` and Saddle discovers them automatically, the same way it discovers resources. Widgets render in ascending `$sort` order. Override `canSee(Request $request): bool` to hide a widget from a request or user. To register widgets explicitly instead of by discovery, call `Saddle::registerWidgets([...])` from a service provider. The discovery path is configurable via `config('saddle.widgets')`.

### Tenancy

Widgets are **not** auto-scoped to a tenant, because they may query anything. When a widget should be tenant-aware, read the bound tenant from the manager and scope your own query:

```php
public function value(Request $request): int
{
    $tenant = app(\SaddlePHP\Saddle::class)->tenant();

    return Horse::query()
        ->when($tenant, fn ($q) => $q->where('ranch_id', $tenant->getKey()))
        ->count();
}
```

One widget that throws while building is skipped (and reported) rather than breaking the whole dashboard.
