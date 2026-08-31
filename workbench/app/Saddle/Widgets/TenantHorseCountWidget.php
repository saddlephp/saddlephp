<?php

declare(strict_types=1);

namespace Workbench\App\Saddle\Widgets;

use Illuminate\Http\Request;
use SaddlePHP\Widgets\StatWidget;
use Workbench\App\Models\Horse;
use Workbench\App\Saddle\HorseResource;

class TenantHorseCountWidget extends StatWidget
{
    public static ?string $resource = HorseResource::class;

    public function label(): string
    {
        return 'Tenant horses';
    }

    public function value(Request $request): int
    {
        return $this->scopeToTenant(Horse::query())->count();
    }
}
