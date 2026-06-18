<?php

declare(strict_types=1);

use SaddlePHP\Saddle;
use Workbench\App\Models\Ranch;
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
