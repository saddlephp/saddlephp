<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Ranch;

it('shares the non-tenant keys plus exactly the tenant keys when tenancy is on', function () {
    $user = $this->actingAsUser();
    $ranch = Ranch::factory()->create(['name' => 'Dusty Creek Ranch']);
    $ranch->users()->attach($user);

    // Same non-tenant keys as tenancy-off (see TenancyOffShapeTest) plus exactly
    // the three tenant-only keys. Scoping without ->etc() enforces exactness.
    $this->get("/admin/{$ranch->getRouteKey()}/resources/horses")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('saddle', fn (Assert $saddle) => $saddle
                ->has('name')->has('accent')->has('version')->has('path')
                ->has('locale')->has('translations')->has('nav')
                ->has('greeting')->has('subgreeting')->has('user')
                ->has('flash')->has('notifications')
                ->has('tenant')->has('tenants')->has('canRegisterTenant')
            )
        );
});
