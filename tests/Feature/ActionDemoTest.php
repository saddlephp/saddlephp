<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;

it('carries the unsaddle row action and the bulk action defs in the horses payload', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/horses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Index')
            ->where('actions.0.name', 'unsaddle')
            ->where('actions.0.label', 'Unsaddle')
            ->where('actions.0.color', 'accent')
            ->where('actions.0.confirm', 'Unsaddle this horse?')
            ->where('bulkActions.0.name', 'saddle-up')
            ->where('bulkActions.0.label', 'Saddle up')
            ->where('bulkActions.0.color', 'ink')
            ->where('bulkActions.0.confirm', null)
            ->where('bulkActions.1.name', 'delete')
            ->where('bulkActions.1.label', 'Delete')
            ->where('bulkActions.1.color', 'accent')
            ->where('bulkActions.1.confirm', 'Delete the selected records?')
        );
});

it('unsaddles a saddled horse and flashes the default success message', function () {
    $this->actingAsUser();
    $horse = Horse::factory()->create(['is_saddled' => true]);

    $this->from('/admin/resources/horses')
        ->post('/admin/resources/horses/actions/unsaddle', ['record' => $horse->id])
        ->assertRedirect('/admin/resources/horses')
        ->assertSessionHas('success', 'Done.');

    expect($horse->fresh()->is_saddled)->toBeFalse();
});

it('saddles up the selected horses in bulk', function () {
    $this->actingAsUser();
    $a = Horse::factory()->create(['is_saddled' => false]);
    $b = Horse::factory()->create(['is_saddled' => false]);

    $this->post('/admin/resources/horses/actions/saddle-up', ['records' => [$a->id, $b->id]])
        ->assertRedirect();

    expect($a->fresh()->is_saddled)->toBeTrue()
        ->and($b->fresh()->is_saddled)->toBeTrue();
});

it('destroys only the selected horses with the bulk delete preset', function () {
    $this->actingAsUser();
    $a = Horse::factory()->create();
    $b = Horse::factory()->create();
    $c = Horse::factory()->create();

    $this->post('/admin/resources/horses/actions/delete', ['records' => [$a->id, $b->id]])
        ->assertRedirect();

    expect(Horse::query()->find($a->id))->toBeNull()
        ->and(Horse::query()->find($b->id))->toBeNull()
        ->and(Horse::query()->find($c->id))->not->toBeNull();
});
