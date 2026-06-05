<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;

it('shows the full form to admins', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/horses/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('fields', fn ($fields) => count(flattenFields(collect($fields)->all())) === 9
                && findField(collect($fields)->all(), 'notes') !== null)
        );
});

it('hides gated fields from non-admins', function () {
    $this->actingAsUser(['is_admin' => false]);

    $this->get('/admin/resources/horses/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('fields', fn ($fields) => count(flattenFields(collect($fields)->all())) === 8
                && findField(collect($fields)->all(), 'notes') === null)
        );
});

it('refuses hidden-field writes from non-admins', function () {
    $this->actingAsUser(['is_admin' => false]);

    $this->post('/admin/resources/horses', ['name' => 'Cisco', 'notes' => 'smuggled'])
        ->assertRedirect('/admin/resources/horses');

    expect(Horse::query()->where('name', 'Cisco')->first()->notes)->toBeNull();
});

it('accepts gated-field writes from admins', function () {
    $this->actingAsUser();

    $this->post('/admin/resources/horses', ['name' => 'Cisco', 'notes' => 'branded']);

    expect(Horse::query()->where('name', 'Cisco')->first()->notes)->toBe('branded');
});

it('refuses hidden-field updates from non-admins', function () {
    $this->actingAsUser(['is_admin' => false]);
    $horse = Horse::factory()->create(['name' => 'Cisco', 'notes' => 'original']);

    $this->put("/admin/resources/horses/{$horse->id}", ['name' => 'Cisco', 'notes' => 'smuggled'])
        ->assertRedirect('/admin/resources/horses');

    expect($horse->fresh()->notes)->toBe('original');
});

it('hides gated field values on the edit form from non-admins', function () {
    $this->actingAsUser(['is_admin' => false]);
    $horse = Horse::factory()->create(['notes' => 'secret']);

    $this->get("/admin/resources/horses/{$horse->id}/edit")
        ->assertOk()
        ->assertDontSee('secret')
        ->assertInertia(fn (Assert $page) => $page
            ->where('fields', fn ($fields) => findField(collect($fields)->all(), 'notes') === null)
        );
});
