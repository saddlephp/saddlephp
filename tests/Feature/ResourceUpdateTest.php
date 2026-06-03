<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;

it('renders the edit form with resolved values', function () {
    $this->actingAsUser();
    $horse = Horse::factory()->create(['name' => 'Cisco']);

    $this->get("/admin/resources/horses/{$horse->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Edit')
            ->where('record.id', $horse->id)
            ->where('record.title', 'Cisco')
            ->where('fields.0.value', 'Cisco')
        );
});

it('updates a record and redirects with a flash', function () {
    $this->actingAsUser();
    $horse = Horse::factory()->create(['name' => 'Cisco', 'is_saddled' => true]);

    $this->put("/admin/resources/horses/{$horse->id}", [
        'name' => 'Dakota', 'breed' => 'mustang', 'notes' => null, 'is_saddled' => false,
    ])
        ->assertRedirect('/admin/resources/horses')
        ->assertSessionHas('success', 'Horse updated.');

    expect($horse->refresh())
        ->name->toBe('Dakota')
        ->is_saddled->toBeFalse();
});

it('404s for records that do not exist', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/horses/999/edit')->assertNotFound();
    $this->put('/admin/resources/horses/999', ['name' => 'X'])->assertNotFound();
});

it('rejects invalid updates', function () {
    $this->actingAsUser();
    $horse = Horse::factory()->create(['name' => 'Cisco']);

    $this->put("/admin/resources/horses/{$horse->id}", ['name' => ''])
        ->assertSessionHasErrors(['name']);

    expect($horse->refresh()->name)->toBe('Cisco');
});
