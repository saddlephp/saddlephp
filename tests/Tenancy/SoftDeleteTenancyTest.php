<?php

declare(strict_types=1);

use Workbench\App\Models\Horse;
use Workbench\App\Models\Ranch;
use Workbench\App\Models\User;

function ranchWithSdMember(string $name, User $member): Ranch
{
    $ranch = Ranch::factory()->create(['name' => $name]);
    $ranch->users()->attach($member);

    return $ranch;
}

it('404s restoring a trashed horse from another tenant', function () {
    $user = $this->actingAsUser();
    $ranchA = ranchWithSdMember('Alpha', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta']);
    $foreign = Horse::factory()->create(['name' => 'Ghost', 'ranch_id' => $ranchB->id]);
    $foreign->delete();

    $this->put("/admin/{$ranchA->getRouteKey()}/resources/horses/{$foreign->id}/restore")->assertNotFound();

    expect(Horse::withTrashed()->find($foreign->id)->trashed())->toBeTrue();
});

it('404s force-deleting a trashed horse from another tenant', function () {
    $user = $this->actingAsUser();
    $ranchA = ranchWithSdMember('Alpha', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta']);
    $foreign = Horse::factory()->create(['name' => 'Ghost', 'ranch_id' => $ranchB->id]);
    $foreign->delete();

    $this->delete("/admin/{$ranchA->getRouteKey()}/resources/horses/{$foreign->id}/force")->assertNotFound();

    $this->assertDatabaseHas('horses', ['id' => $foreign->id]);
});
