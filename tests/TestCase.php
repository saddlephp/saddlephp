<?php

declare(strict_types=1);

namespace RodeoPHP\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use RodeoPHP\Rodeo;
use Workbench\App\Models\User;
use Workbench\App\Rodeo\HorseResource;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(Rodeo::class)->register([HorseResource::class]);

        Gate::guessPolicyNamesUsing(fn () => null);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('rodeo.middleware', ['web', 'auth']);
        $app['config']->set('inertia.testing.ensure_pages_exist', false);
    }

    protected function actingAsUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }
}
