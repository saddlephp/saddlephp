<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;

beforeEach(function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Bandit', 'breed' => 'mustang']);
    Horse::factory()->create(['name' => 'Cisco', 'breed' => 'quarter']);
    Horse::factory()->create(['name' => 'Willow', 'breed' => 'appaloosa']);
});

it('filters via search across searchable columns', function () {
    $this->get('/admin/resources/horses?search=cis')
        ->assertInertia(fn (Assert $page) => $page
            ->count('rows.data', 1)
            ->where('rows.data.0.cells.name', 'Cisco')
            ->where('query.search', 'cis')
        );
});

it('sorts by a sortable column ascending and descending', function () {
    $this->get('/admin/resources/horses?sort=name&direction=asc')
        ->assertInertia(fn (Assert $page) => $page->where('rows.data.0.cells.name', 'Bandit'));

    $this->get('/admin/resources/horses?sort=name&direction=desc')
        ->assertInertia(fn (Assert $page) => $page->where('rows.data.0.cells.name', 'Willow'));
});

it('falls back to key desc for non-sortable sort params', function () {
    $this->get('/admin/resources/horses?sort=notes')
        ->assertInertia(fn (Assert $page) => $page
            ->where('query.sort', 'id')
            ->where('query.direction', 'desc')
            ->where('rows.data.0.cells.name', 'Willow')
        );
});

it('matches an underscore in a search term literally rather than as a wildcard', function () {
    Horse::factory()->create(['name' => 'Rio_Grande', 'breed' => 'criollo']);
    Horse::factory()->create(['name' => 'RioXGrande', 'breed' => 'criollo']);

    $this->get('/admin/resources/horses?search=Rio_Grande')
        ->assertInertia(fn (Assert $page) => $page
            ->count('rows.data', 1)
            ->where('rows.data.0.cells.name', 'Rio_Grande')
        );
});

it('matches a percent sign in a search term literally', function () {
    Horse::factory()->create(['name' => '50% Off', 'breed' => 'draft']);

    $this->get('/admin/resources/horses?search=50%25 Off')
        ->assertInertia(fn (Assert $page) => $page
            ->count('rows.data', 1)
            ->where('rows.data.0.cells.name', '50% Off')
        );
});
