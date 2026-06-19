<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use SaddlePHP\Saddle;
use Workbench\App\Saddle\HorseResource;
use Workbench\App\Saddle\RanchResource;
use Workbench\App\Saddle\RiderResource;
use Workbench\App\Saddle\Widgets\HorseCountWidget;
use Workbench\App\Saddle\Widgets\HorsesByBreedWidget;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->make(Saddle::class)->register([HorseResource::class, RiderResource::class, RanchResource::class]);

        $this->app->make(Saddle::class)->registerWidgets([HorseCountWidget::class, HorsesByBreedWidget::class]);

        $this->app->make(Saddle::class)
            ->script('/vendor/saddle-demo/rating-field.js')
            ->style('/vendor/saddle-demo/rating-field.css');
    }
}
