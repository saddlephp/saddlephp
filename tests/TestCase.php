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
use Workbench\App\Saddle\RiderResource;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(Saddle::class)->register([HorseResource::class, RiderResource::class]);

        Gate::guessPolicyNamesUsing(fn () => null);
    }

    protected function defineEnvironment($app): void
    {
        // Defaults to sqlite::memory: for speed, but honours SADDLE_TEST_DB so
        // CI can run the same suite against MySQL and Postgres. (Deliberately
        // not DB_CONNECTION: phpunit.xml already sets that to the connection
        // *name*, "testing", not a driver.)
        //
        // This matters more than it looks. SQLite turns an unknown
        // double-quoted identifier into a string literal, so a sort on a column
        // that does not exist returns rows and a 200 here while 500ing on
        // MySQL; it has no default LIKE escape character, so an escaped search
        // term silently matches nothing; and it coerces types the other two
        // reject. Pinning the suite to it made a whole class of bug invisible.
        $driver = env('SADDLE_TEST_DB', 'sqlite');

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', $driver === 'sqlite'
            ? ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']
            : [
                'driver' => $driver,
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', $driver === 'mysql' ? '3306' : '5432'),
                'database' => env('DB_DATABASE', 'saddle'),
                'username' => env('DB_USERNAME', $driver === 'mysql' ? 'root' : 'postgres'),
                'password' => env('DB_PASSWORD', 'saddle'),
                'charset' => $driver === 'mysql' ? 'utf8mb4' : 'utf8',
                'prefix' => '',
            ]);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('saddle.middleware', ['web', 'auth']);
        $app['config']->set('inertia.testing.ensure_pages_exist', false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * Default user is privileged (is_admin => true); tests exercising gates must opt out explicitly via ['is_admin' => false].
     */
    protected function actingAsUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge(['is_admin' => true], $attributes));
        $this->actingAs($user);

        return $user;
    }
}
