<?php

declare(strict_types=1);

use Workbench\App\Models\Ranch;
use Workbench\App\Models\Rider;
use Workbench\App\Models\User;
use Workbench\App\Saddle\RiderResource;

// RiderResource is the tenant-scoped PARENT here (riders belong to a ranch) and
// it manages its horses through a relation manager. Toggle the scope per file
// and restore it so the static never leaks into other suites.
beforeEach(fn () => RiderResource::$tenant = 'ranch');
afterEach(fn () => RiderResource::$tenant = null);

function ranchWithRelMember(string $name, User $member): Ranch
{
    $ranch = Ranch::factory()->create(['name' => $name]);
    $ranch->users()->attach($member);

    return $ranch;
}

it('404s a relation list for a parent that belongs to another tenant', function () {
    $user = $this->actingAsUser();
    $ranchA = ranchWithRelMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);
    $riderB = Rider::factory()->create(['name' => 'Bob', 'ranch_id' => $ranchB->id]);
    $riderB->horses()->create(['name' => 'Bandit']);

    $this->getJson("/admin/{$ranchA->getRouteKey()}/resources/riders/{$riderB->id}/relations/horses")
        ->assertNotFound();
});

it('404s relation writes against a parent in another tenant and leaves data intact', function () {
    $user = $this->actingAsUser();
    $ranchA = ranchWithRelMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);
    $riderB = Rider::factory()->create(['name' => 'Bob', 'ranch_id' => $ranchB->id]);
    $horseB = $riderB->horses()->create(['name' => 'Bandit']);

    $base = "/admin/{$ranchA->getRouteKey()}/resources/riders/{$riderB->id}/relations/horses";

    $this->post($base, ['name' => 'Smuggled'])->assertNotFound();
    $this->put("{$base}/{$horseB->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->delete("{$base}/{$horseB->id}")->assertNotFound();

    expect($riderB->horses()->count())->toBe(1)
        ->and($horseB->fresh()->name)->toBe('Bandit');
});

it('lets a member manage their own tenant parent\'s relation', function () {
    $user = $this->actingAsUser();
    $ranchA = ranchWithRelMember('Alpha Ranch', $user);
    $riderA = Rider::factory()->create(['name' => 'Alice', 'ranch_id' => $ranchA->id]);

    $base = "/admin/{$ranchA->getRouteKey()}/resources/riders/{$riderA->id}/relations/horses";

    $this->getJson($base)->assertOk()->assertJsonPath('key', 'horses');
    $this->post($base, ['name' => 'Cisco'])->assertRedirect();

    expect($riderA->horses()->count())->toBe(1)
        ->and($riderA->horses()->first()->name)->toBe('Cisco');
});
