<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

it('keeps the v0.5 saddle prop shape when tenancy is off', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/horses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('saddle.path', 'admin')
            ->missing('saddle.tenant')
            ->missing('saddle.tenants')
        );
});

it('shares exactly the non-tenant saddle keys when tenancy is off', function () {
    $this->actingAsUser();

    // Scoping into `saddle` without ->etc() asserts these are ALL the keys, so
    // a new shared prop can't silently ship only under the tenancy-on branch.
    $this->get('/admin/resources/horses')
        ->assertInertia(fn (Assert $page) => $page
            ->has('saddle', fn (Assert $saddle) => $saddle
                ->has('name')->has('accent')->has('version')->has('path')
                ->has('locale')->has('translations')->has('nav')
                ->has('greeting')->has('subgreeting')->has('user')
                ->has('flash')->has('notifications')
            )
        );
});
