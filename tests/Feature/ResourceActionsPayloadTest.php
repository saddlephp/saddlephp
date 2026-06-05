<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

it('riders index payload has an empty actions array for an actionless resource', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/riders')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Index')
            ->where('actions', [])
        );
});

it('riders index payload has an empty bulkActions array for an actionless resource', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/riders')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Index')
            ->where('bulkActions', [])
        );
});
