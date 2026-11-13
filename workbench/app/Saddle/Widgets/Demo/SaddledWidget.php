<?php

declare(strict_types=1);

namespace Workbench\App\Saddle\Widgets\Demo;

use Illuminate\Http\Request;
use SaddlePHP\Widgets\StatWidget;
use Workbench\App\Models\Horse;
use Workbench\App\Saddle\HorseResource;

/** Demo-only tile, registered when SADDLE_DEMO=1 (see WorkbenchServiceProvider). */
class SaddledWidget extends StatWidget
{
    public static int $sort = 1;

    public static ?string $resource = HorseResource::class;

    public function label(): string
    {
        return 'Saddled';
    }

    public function value(Request $request): string|int
    {
        return Horse::query()->where('is_saddled', true)->count();
    }

    public function description(Request $request): ?string
    {
        return 'Ready to ride';
    }

    public function chart(Request $request): array
    {
        return [4, 6, 5, 7, 6, 8, 7];
    }
}
