<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use SaddlePHP\Tests\Fixtures\LockedDownHorsePolicy;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Rider;
use Workbench\App\Policies\HorsePolicy;

it('serves relation options as json', function () {
    $this->actingAsUser();
    Rider::factory()->create(['name' => 'Billie']);
    $amos = Rider::factory()->create(['name' => 'Amos']);

    $this->getJson('/admin/resources/horses/options/rider_id')
        ->assertOk()
        ->assertJsonCount(2, 'options')
        ->assertJsonPath('options.0.value', $amos->id)
        ->assertJsonPath('options.0.label', 'Amos');
});

it('narrows options with a search term', function () {
    $this->actingAsUser();
    Rider::factory()->create(['name' => 'Amos']);
    Rider::factory()->create(['name' => 'Billie']);

    $this->getJson('/admin/resources/horses/options/rider_id?search=bil')
        ->assertOk()
        ->assertJsonCount(1, 'options')
        ->assertJsonPath('options.0.label', 'Billie');
});

it('rejects guests', function () {
    $this->getJson('/admin/resources/horses/options/rider_id')->assertUnauthorized();
});

it('returns 404 for unknown fields', function () {
    $this->actingAsUser();

    $this->getJson('/admin/resources/horses/options/nope')->assertNotFound();
});

it('returns 404 for fields that are not relations', function () {
    $this->actingAsUser();

    $this->getJson('/admin/resources/horses/options/name')->assertNotFound();
});

it('denies users who can neither create nor update the resource', function () {
    Gate::policy(Horse::class, LockedDownHorsePolicy::class);
    $this->actingAsUser();

    $this->getJson('/admin/resources/horses/options/rider_id')->assertForbidden();
});

it('serves options to users who can update but not create', function () {
    Gate::policy(Horse::class, HorsePolicy::class);
    $this->actingAsUser();
    Rider::factory()->create(['name' => 'Amos']);

    $this->getJson('/admin/resources/horses/options/rider_id')
        ->assertOk()
        ->assertJsonCount(1, 'options');
});
