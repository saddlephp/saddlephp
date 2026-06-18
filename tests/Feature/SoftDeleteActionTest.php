<?php

declare(strict_types=1);

use Workbench\App\Models\Horse;
use Workbench\App\Models\Rider;

it('restores a trashed record', function () {
    $this->actingAsUser();
    $horse = Horse::factory()->create(['name' => 'Archived']);
    $horse->delete();

    $this->put("/admin/resources/horses/{$horse->id}/restore")
        ->assertRedirect('/admin/resources/horses');

    expect(Horse::query()->find($horse->id))->not->toBeNull();
});

it('404s restore on a resource that is not soft-deletable', function () {
    $this->actingAsUser();
    $rider = Rider::factory()->create();

    $this->put("/admin/resources/riders/{$rider->id}/restore")->assertNotFound();
});

it('permanently deletes a trashed record', function () {
    $this->actingAsUser();
    $horse = Horse::factory()->create(['name' => 'Archived']);
    $horse->delete();

    $this->delete("/admin/resources/horses/{$horse->id}/force")
        ->assertRedirect('/admin/resources/horses');

    $this->assertDatabaseMissing('horses', ['id' => $horse->id]);
});

it('404s force-delete on a resource that is not soft-deletable', function () {
    $this->actingAsUser();
    $rider = Rider::factory()->create();

    $this->delete("/admin/resources/riders/{$rider->id}/force")->assertNotFound();
});
