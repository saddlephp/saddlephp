<?php

declare(strict_types=1);

namespace Workbench\App\Saddle\Widgets;

use Illuminate\Http\Request;
use SaddlePHP\Widgets\ChartWidget;
use Workbench\App\Models\Horse;
use Workbench\App\Saddle\HorseResource;

class HorsesByBreedWidget extends ChartWidget
{
    public static int $sort = 1;

    public static ?string $resource = HorseResource::class;

    public function heading(): string
    {
        return 'Horses by breed';
    }

    public function data(Request $request): array
    {
        return $this->scopeToTenant(Horse::query())
            ->selectRaw('breed, count(*) as c')
            ->whereNotNull('breed')
            ->groupBy('breed')
            ->pluck('c', 'breed')
            ->all();
    }
}
