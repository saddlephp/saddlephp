<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;

it('filters rows by a declared select filter', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Cisco', 'breed' => 'quarter']);
    Horse::factory()->create(['name' => 'Scout', 'breed' => 'mustang']);

    $this->get('/admin/resources/horses?filter[breed]=quarter')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->count('rows.data', 1)
            ->where('rows.data.0.cells.name', 'Cisco')
            ->where('query.filter.breed', 'quarter')
        );
});

it('filters rows by a boolean filter', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Cisco', 'is_saddled' => true]);
    Horse::factory()->create(['name' => 'Scout', 'is_saddled' => false]);

    $this->get('/admin/resources/horses?filter[is_saddled]=0')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->count('rows.data', 1)
            ->where('rows.data.0.cells.name', 'Scout')
        );
});

it('combines filters with search', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Cisco', 'breed' => 'quarter', 'is_saddled' => true]);
    Horse::factory()->create(['name' => 'Cimarron', 'breed' => 'quarter', 'is_saddled' => false]);
    Horse::factory()->create(['name' => 'Cinnamon', 'breed' => 'mustang', 'is_saddled' => true]);

    $this->get('/admin/resources/horses?search=Ci&filter[breed]=quarter&filter[is_saddled]=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->count('rows.data', 1)
            ->where('rows.data.0.cells.name', 'Cisco')
        );
});

it('ignores undeclared filters and invalid values', function () {
    $this->actingAsUser();
    Horse::factory()->count(2)->create(['breed' => 'quarter']);

    $this->get('/admin/resources/horses?filter[notes]=x&filter[breed]=bogus&filter[is_saddled]=maybe')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->count('rows.data', 2)
            ->where('query.filter', [])
        );
});

it('ignores a malformed filter parameter', function () {
    $this->actingAsUser();
    Horse::factory()->count(2)->create();

    $this->get('/admin/resources/horses?filter=quarter')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->count('rows.data', 2));
});

it('serializes filter definitions for the panel', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/horses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->count('filters', 2)
            ->where('filters.0.type', 'select')
            ->where('filters.0.name', 'breed')
            ->count('filters.0.options', 3)
            ->where('filters.1.type', 'boolean')
        );
});
