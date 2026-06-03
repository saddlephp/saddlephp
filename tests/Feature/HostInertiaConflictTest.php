<?php

declare(strict_types=1);

namespace RodeoPHP\Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use RodeoPHP\Tests\TestCase;
use Workbench\App\Http\Middleware\HostInertiaMiddleware;

class HostInertiaConflictTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        // Simulate a host app that runs its own Inertia middleware on the
        // global web group with its own root view.
        $app['router']->pushMiddlewareToGroup('web', HostInertiaMiddleware::class);
    }

    public function test_panel_keeps_its_own_root_view_when_host_runs_inertia(): void
    {
        $this->actingAsUser();

        $this->get('/admin')
            ->assertOk()
            ->assertViewIs('rodeo::app')
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
    }
}
