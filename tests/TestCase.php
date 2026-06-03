<?php

declare(strict_types=1);

namespace SaddlePHP\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use SaddlePHP\Saddle;
use Workbench\App\Models\User;
use Workbench\App\Saddle\HorseResource;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(Saddle::class)->register([HorseResource::class]);

        Gate::guessPolicyNamesUsing(fn () => null);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('saddle.middleware', ['web', 'auth']);
        $app['config']->set('inertia.testing.ensure_pages_exist', false);
    }

    protected function actingAsUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }
}
