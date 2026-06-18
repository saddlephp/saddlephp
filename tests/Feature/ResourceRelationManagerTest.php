<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use SaddlePHP\Saddle;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Ranch;
use Workbench\App\Models\User;
use Workbench\App\Saddle\RanchResource;
use Workbench\App\Saddle\RelationManagers\HorsesRelationManager;

beforeEach(function () {
    $this->app->make(Saddle::class)->register([RanchResource::class]);
});

it('registers relation managers on a resource', function () {
    expect(RanchResource::relations())->toBe([HorsesRelationManager::class]);
});

it('resolves the ranch resource by uri key', function () {
    expect(app(Saddle::class)->resourceFor('ranches'))->toBe(RanchResource::class);
});

it('lists only the parent\'s related records', function () {
    $this->actingAsUser();
    $ranchA = Ranch::factory()->create();
    $ranchB = Ranch::factory()->create();
    $ranchA->horses()->create(['name' => 'Cisco']);
    $ranchB->horses()->create(['name' => 'Dakota']);

    $this->getJson("/admin/resources/ranches/{$ranchA->id}/relations/horses")
        ->assertOk()
        ->assertJsonPath('key', 'horses')
        ->assertJsonPath('rows.data.0.title', 'Cisco')
        ->assertJsonCount(1, 'rows.data');
});

it('404s for an unknown relation key', function () {
    $this->actingAsUser();
    $ranch = Ranch::factory()->create();

    $this->getJson("/admin/resources/ranches/{$ranch->id}/relations/nope")->assertNotFound();
});

it('creates a related record through the parent relationship', function () {
    $this->actingAsUser();
    $ranch = Ranch::factory()->create();

    $this->post("/admin/resources/ranches/{$ranch->id}/relations/horses", [
        'name' => 'Cisco', 'is_saddled' => true,
    ])->assertRedirect();

    expect($ranch->horses()->count())->toBe(1)
        ->and($ranch->horses()->first()->name)->toBe('Cisco');
});

it('validates the related form on create', function () {
    $this->actingAsUser();
    $ranch = Ranch::factory()->create();

    $this->post("/admin/resources/ranches/{$ranch->id}/relations/horses", ['name' => ''])
        ->assertSessionHasErrors(['name']);

    expect($ranch->horses()->count())->toBe(0);
});

it('forbids creating when the related policy denies create', function () {
    Gate::policy(Horse::class, DenyHorseCreatePolicy::class);
    $this->actingAsUser();
    $ranch = Ranch::factory()->create();

    $this->post("/admin/resources/ranches/{$ranch->id}/relations/horses", ['name' => 'Cisco'])
        ->assertForbidden();

    expect($ranch->horses()->count())->toBe(0);
});

class DenyHorseCreatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }
}
