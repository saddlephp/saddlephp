<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use SaddlePHP\Saddle;
use Workbench\App\Saddle\HorseResource;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->make(Saddle::class)->register([HorseResource::class]);
    }
}
