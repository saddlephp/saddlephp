<?php

declare(strict_types=1);

namespace Workbench\App\Saddle\Widgets;

use Illuminate\Http\Request;
use SaddlePHP\Widgets\ChartWidget;
use Workbench\App\Models\Horse;

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
            ->whereNotNull('breed')
            ->groupBy('breed')
            ->pluck('c', 'breed')
            ->all();
    }
}
