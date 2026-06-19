<?php

declare(strict_types=1);

namespace Workbench\App\Saddle\Widgets;

use Illuminate\Http\Request;
use SaddlePHP\Saddle;
use SaddlePHP\Widgets\StatWidget;
use Workbench\App\Models\Horse;

class TenantHorseCountWidget extends StatWidget
{
    public function label(): string
    {
        return 'Tenant horses';
    }

    public function value(Request $request): int
    {
        $tenant = app(Saddle::class)->tenant();

        return Horse::query()
            ->when($tenant, fn ($q) => $q->where('ranch_id', $tenant->getKey()))
            ->count();
    }
}
