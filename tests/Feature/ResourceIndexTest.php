<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;

it('lists records with columns, cells and abilities', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Cisco', 'breed' => 'quarter']);

    $this->get('/admin/resources/horses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Index')
            ->where('resource.uriKey', 'horses')
            ->where('resource.label', 'Horses')
            ->where('resource.canCreate', true)
            ->where('columns.0.name', 'name')
            ->count('rows.data', 1)
            ->where('rows.data.0.cells.name', 'Cisco')
            ->where('rows.data.0.can.update', true)
            ->where('rows.data.0.title', 'Cisco')
        );
});

it('paginates at the configured page size', function () {
    $this->actingAsUser();
    config(['rodeo.per_page' => 10]);
    Horse::factory()->count(15)->create();

    $this->get('/admin/resources/horses')
        ->assertInertia(fn (Assert $page) => $page
            ->count('rows.data', 10)
            ->where('rows.total', 15)
            ->where('rows.last_page', 2)
        );
});

it('404s for unknown resource keys', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/unicorns')->assertNotFound();
});
