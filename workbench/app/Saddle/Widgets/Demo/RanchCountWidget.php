<?php

declare(strict_types=1);

namespace Workbench\App\Saddle\Widgets\Demo;

use Illuminate\Http\Request;
use SaddlePHP\Widgets\StatWidget;
use Workbench\App\Models\Ranch;
use Workbench\App\Saddle\RanchResource;

/** Demo-only tile, registered when SADDLE_DEMO=1 (see WorkbenchServiceProvider). */
class RanchCountWidget extends StatWidget
{
    public static int $sort = 3;

    public static ?string $resource = RanchResource::class;

    public function label(): string
    {
        return 'Ranches';
    }

    public function value(Request $request): string|int
    {
        return Ranch::query()->count();
    }

    public function description(Request $request): ?string
    {
        return 'Across three counties';
    }

    public function chart(Request $request): array
    {
        return [1, 1, 2, 2, 2, 3, 3];
    }
}
