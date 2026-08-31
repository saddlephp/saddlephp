<?php

declare(strict_types=1);

namespace Workbench\App\Saddle\Widgets;

use Illuminate\Http\Request;
use SaddlePHP\Widgets\StatWidget;
use Workbench\App\Models\Horse;
use Workbench\App\Saddle\HorseResource;

class HorseCountWidget extends StatWidget
{
    public static int $sort = 0;

    /** Gates the tile with the same policy that gates the table. */
    public static ?string $resource = HorseResource::class;

    public function label(): string
    {
        return 'Horses';
    }

    public function value(Request $request): int
    {
        return $this->scopeToTenant(Horse::query())->count();
    }

    public function description(Request $request): ?string
    {
        return $this->scopeToTenant(Horse::query())->where('is_saddled', true)->count().' saddled';
    }

    public function chart(Request $request): array
    {
        return [3, 5, 2, 8, 6, 9];
    }
}
