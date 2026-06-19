<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Ranch;
use Workbench\App\Saddle\RanchRegistration;

it('renders the registration page with the handler fields', function () {
    config()->set('saddle.tenancy.registration', RanchRegistration::class);
    $this->actingAsUser();

    $this->get('/admin/register')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenancy/Register')
            ->where('fields', fn ($f) => findField(collect($f)->all(), 'name') !== null));
});

it('creates a tenant and redirects to it', function () {
    config()->set('saddle.tenancy.registration', RanchRegistration::class);
    $user = $this->actingAsUser();

    $response = $this->post('/admin/register', ['name' => 'New Ranch']);

    $ranch = Ranch::where('name', 'New Ranch')->firstOrFail();
    expect($ranch->users()->whereKey($user->getKey())->exists())->toBeTrue();
    $response->assertRedirect("/admin/{$ranch->getRouteKey()}");
});

it('404s registration when disabled', function () {
    config()->set('saddle.tenancy.registration', null);
    $this->actingAsUser();

    $this->get('/admin/register')->assertNotFound();
});
