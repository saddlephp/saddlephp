<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use SaddlePHP\Tests\Fixtures\DenyViewAnyPolicy;
use Workbench\App\Models\Rider;

it('renders the dashboard with shared panel props', function () {
    $this->actingAsUser();

    $this->get('/admin')
        ->assertOk()
        ->assertViewIs('saddle::app')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('saddle.name', 'SaddlePHP')
            ->where('saddle.path', 'admin')
            ->where('saddle.nav.0.items.0.uriKey', 'horses')
            ->has('saddle.user.name')
        );
});

it('redirects guests to login', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

it('hides nav entries the user may not view', function () {
    Gate::policy(Rider::class, DenyViewAnyPolicy::class);
    $this->actingAsUser();

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('saddle.nav', function ($nav) {
                $items = collect($nav)->flatMap(fn ($group) => collect($group['items'])->pluck('uriKey'));

                return $items->contains('horses') && ! $items->contains('riders');
            })
        );
});
