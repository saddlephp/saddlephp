<?php

declare(strict_types=1);

namespace RodeoPHP\Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use RodeoPHP\Tests\TestCase;

class CustomPathTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('rodeo.path', 'ranch');
    }

    public function test_panel_mounts_at_a_custom_path(): void
    {
        $this->actingAsUser();

        $this->get('/ranch')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));

        $this->get('/admin')->assertNotFound();
    }
}
