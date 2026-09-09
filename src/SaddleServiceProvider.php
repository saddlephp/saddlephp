<?php

declare(strict_types=1);

namespace SaddlePHP;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestReceived;
use SaddlePHP\Console\InstallCommand;
use SaddlePHP\Console\ResourceMakeCommand;
use SaddlePHP\Console\ResourceRelationMakeCommand;
use SaddlePHP\Console\UpgradeCommand;
use SaddlePHP\Http\Controllers\TenantRegisterController;
use SaddlePHP\Http\Middleware\HandleSaddleRequests;
use SaddlePHP\Http\Middleware\ResolveSaddleTenant;

class SaddleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/saddle.php', 'saddle');

        $this->app->singleton(Saddle::class, static fn (): Saddle => new Saddle);
        $this->app->alias(Saddle::class, 'saddle');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'saddle');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'saddle');

        $this->registerRoutes();

        $this->resetTenantBetweenRequests();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/saddle.php' => $this->app->configPath('saddle.php'),
            ], 'saddle-config');

            // Two tags on purpose. `laravel-assets` is the convention a fresh
            // Laravel application's composer.json already calls on
            // post-update-cmd (`vendor:publish --tag=laravel-assets --force`),
            // and it is the only thing that republishes the compiled bundle
            // automatically on `composer update`.
            //
            // Published under `saddle-assets` alone, upgrading the package
            // updated the PHP and left public/vendor/saddle/ holding the
            // PREVIOUS bundle. Every server-side check passed -- the version
            // constant, the new classes, the new methods -- while the panel
            // still rendered the old frontend. A silent failure whose only
            // symptom is "the feature I upgraded for isn't there", hit by every
            // consumer on every upgrade.
            $this->publishes([
                __DIR__.'/../dist' => public_path('vendor/saddle'),
            ], ['saddle-assets', 'laravel-assets']);

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'saddle-migrations');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath('vendor/saddle'),
            ], 'saddle-lang');

            $this->commands([
                ResourceMakeCommand::class,
                ResourceRelationMakeCommand::class,
                InstallCommand::class,
                UpgradeCommand::class,
            ]);
        }
    }

    /**
     * On Octane the Saddle singleton outlives a single request, so the tenant
     * bound during one request would otherwise carry into the next. Clear it
     * when each fresh request is received. Guarded by class existence so the
     * package never hard-depends on Octane.
     */
    protected function resetTenantBetweenRequests(): void
    {
        if (class_exists(RequestReceived::class)) {
            Event::listen(
                RequestReceived::class,
                fn () => $this->app->make(Saddle::class)->forgetTenant(),
            );
        }
    }

    protected function registerRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $tenancyOn = config('saddle.tenancy.model') !== null;

        $prefix = config('saddle.path', 'admin');
        // Cast: a host writing 'middleware' => 'web' is valid-looking config
        // that used to fatal on every request when the appends below ran
        // against a string. The register group below already casts.
        $middleware = (array) config('saddle.middleware', ['web', 'auth']);

        if ($tenancyOn) {
            $prefix .= '/{tenant}';
            // ResolveSaddleTenant binds the tenant before HandleSaddleRequests
            // shares props that depend on it, and enforces membership (403)
            // before anything renders.
            $middleware[] = ResolveSaddleTenant::class;
        }

        $middleware[] = HandleSaddleRequests::class;

        // Tenant registration mounts at /{path}/register — WITHOUT the {tenant}
        // segment (the user has no tenant yet) and without ResolveSaddleTenant.
        // Registered BEFORE the main group so the literal `register` wins over
        // the tenant dashboard route (/{path}/{tenant}). The route exists
        // whenever tenancy is on; the controller 404s when no handler is set.
        if ($tenancyOn) {
            Route::prefix(config('saddle.path', 'admin'))
                ->middleware(array_merge((array) config('saddle.middleware', ['web', 'auth']), [HandleSaddleRequests::class]))
                ->name('saddle.register.')
                ->group(function () {
                    Route::get('/register', [TenantRegisterController::class, 'show'])->name('show');
                    Route::post('/register', [TenantRegisterController::class, 'store'])->name('store');
                });
        }

        Route::prefix($prefix)
            ->middleware($middleware)
            ->name('saddle.')
            ->group(__DIR__.'/../routes/saddle.php');
    }
}
