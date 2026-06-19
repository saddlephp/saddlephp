<?php

declare(strict_types=1);

namespace Workbench\App\Saddle\Widgets;

use Illuminate\Http\Request;
use SaddlePHP\Widgets\StatWidget;
use Workbench\App\Models\Horse;

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

    public function chart(Request $request): array
    {
        return [3, 5, 2, 8, 6, 9];
    }
}
