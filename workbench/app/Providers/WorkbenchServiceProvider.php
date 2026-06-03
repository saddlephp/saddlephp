<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use RodeoPHP\Rodeo;
use Workbench\App\Rodeo\HorseResource;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->make(Rodeo::class)->register([HorseResource::class]);
    }
}
