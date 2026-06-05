<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use SaddlePHP\Saddle;
use Workbench\App\Saddle\HorseResource;
use Workbench\App\Saddle\RiderResource;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->make(Saddle::class)->register([HorseResource::class, RiderResource::class]);

        $this->app->make(Saddle::class)
            ->script('/vendor/saddle-demo/rating-field.js')
            ->style('/vendor/saddle-demo/rating-field.css');
    }
}
