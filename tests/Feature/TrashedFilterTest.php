<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;

it('hides trashed records by default and offers the trashed filter', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Active']);
    Horse::factory()->create(['name' => 'Archived'])->delete();

    $this->get('/admin/resources/horses')
        ->assertInertia(fn (Assert $page) => $page
            ->where('rows.data', fn ($rows) => collect($rows)->pluck('title')->all() === ['Active'])
            ->where('filters', fn ($filters) => collect($filters)->contains(fn ($f) => $f['name'] === 'trashed'))
        );
});

it('shows trashed and active records with the with filter', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Active']);
    Horse::factory()->create(['name' => 'Archived'])->delete();

    $this->get('/admin/resources/horses?filter[trashed]=with')
        ->assertInertia(fn (Assert $page) => $page
            ->where('rows.data', fn ($rows) => collect($rows)->pluck('title')->sort()->values()->all() === ['Active', 'Archived'])
        );
});

it('shows only trashed records with the only filter', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Active']);
    Horse::factory()->create(['name' => 'Archived'])->delete();

    $this->get('/admin/resources/horses?filter[trashed]=only')
        ->assertInertia(fn (Assert $page) => $page
            ->where('rows.data', fn ($rows) => collect($rows)->pluck('title')->all() === ['Archived'])
        );
});

it('offers no trashed filter for a non-soft-deletable resource', function () {
    $this->actingAsUser();
    \Workbench\App\Models\Rider::factory()->create();

    $this->get('/admin/resources/riders')
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters', fn ($filters) => ! collect($filters)->contains(fn ($f) => $f['name'] === 'trashed'))
        );
});
