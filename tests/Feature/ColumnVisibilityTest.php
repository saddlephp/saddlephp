<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;

beforeEach(function () {
    Horse::factory()->create(['name' => 'Bandit', 'breed' => 'mustang', 'notes' => 'colic risk']);
    Horse::factory()->create(['name' => 'Cisco', 'breed' => 'quarter', 'notes' => 'sound']);
});

it('includes a gated column for a user who passes the gate', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/horses')
        ->assertInertia(fn (Assert $page) => $page
            ->has('rows.data.0.cells.notes')
            ->where('columns', fn ($columns) => collect($columns)->contains('name', 'notes'))
        );
});

it('omits a gated column from the index cells for a user who fails the gate', function () {
    $this->actingAsUser(['is_admin' => false]);

    $this->get('/admin/resources/horses')
        ->assertInertia(fn (Assert $page) => $page
            ->missing('rows.data.0.cells.notes')
        );
});

it('omits a gated column from the columns payload for a user who fails the gate', function () {
    $this->actingAsUser(['is_admin' => false]);

    $this->get('/admin/resources/horses')
        ->assertInertia(fn (Assert $page) => $page
            ->where('columns', fn ($columns) => ! collect($columns)->contains('name', 'notes'))
        );
});

it('does not let a hidden column act as a sort oracle', function () {
    $this->actingAsUser(['is_admin' => false]);

    $this->get('/admin/resources/horses?sort=notes')
        ->assertInertia(fn (Assert $page) => $page
            ->where('query.sort', 'id')
            ->where('query.direction', 'desc')
        );
});

it('does not let a hidden column act as a search oracle', function () {
    $this->actingAsUser(['is_admin' => false]);

    // "colic" appears only in the gated notes column. A user who cannot see the
    // column must not be able to confirm its contents by searching for them.
    $this->get('/admin/resources/horses?search=colic')
        ->assertInertia(fn (Assert $page) => $page->count('rows.data', 0));
});

it('omits a gated column from the CSV export for a user who fails the gate', function () {
    $this->actingAsUser(['is_admin' => false]);

    $response = $this->get('/admin/resources/horses/export');
    $response->assertOk();

    $csv = $response->streamedContent();

    expect($csv)->not->toContain('colic risk')
        ->and($csv)->not->toContain('Notes');
});

it('includes a gated column in the CSV export for a user who passes the gate', function () {
    $this->actingAsUser();

    $response = $this->get('/admin/resources/horses/export');
    $response->assertOk();

    expect($response->streamedContent())->toContain('colic risk');
});
