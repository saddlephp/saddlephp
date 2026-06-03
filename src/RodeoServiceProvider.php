<?php

declare(strict_types=1);

namespace RodeoPHP;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RodeoPHP\Console\InstallCommand;
use RodeoPHP\Console\ResourceMakeCommand;
use RodeoPHP\Console\UpgradeCommand;
use RodeoPHP\Http\Middleware\HandleRodeoRequests;

class RodeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rodeo.php', 'rodeo');

        $this->app->singleton(Rodeo::class, static fn (): Rodeo => new Rodeo);
        $this->app->alias(Rodeo::class, 'rodeo');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rodeo');

        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/rodeo.php' => $this->app->configPath('rodeo.php'),
            ], 'rodeo-config');

            $this->publishes([
                __DIR__.'/../dist' => public_path('vendor/rodeo'),
            ], 'rodeo-assets');

            $this->commands([
                ResourceMakeCommand::class,
                InstallCommand::class,
                UpgradeCommand::class,
            ]);
        }
    }

    protected function registerRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::prefix(config('rodeo.path', 'admin'))
            ->middleware([
                ...config('rodeo.middleware', ['web', 'auth']),
                HandleRodeoRequests::class,
            ])
            ->name('rodeo.')
            ->group(__DIR__.'/../routes/rodeo.php');
    }
}
