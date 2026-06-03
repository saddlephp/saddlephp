<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

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
