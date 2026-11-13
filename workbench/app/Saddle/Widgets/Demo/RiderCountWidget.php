<?php

declare(strict_types=1);

namespace Workbench\App\Saddle\Widgets\Demo;

use Illuminate\Http\Request;
use SaddlePHP\Widgets\StatWidget;
use Workbench\App\Models\Rider;
use Workbench\App\Saddle\RiderResource;

/** Demo-only tile, registered when SADDLE_DEMO=1 (see WorkbenchServiceProvider). */
class RiderCountWidget extends StatWidget
{
    public static int $sort = 2;

    public static ?string $resource = RiderResource::class;

    public function label(): string
    {
        return 'Riders';
    }

    public function value(Request $request): string|int
    {
        return Rider::query()->count();
    }

    public function description(Request $request): ?string
    {
        return '2 joined this month';
    }

    public function chart(Request $request): array
    {
        return [2, 3, 3, 4, 5, 5, 6];
    }
}
