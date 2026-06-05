<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Ranch;
use Workbench\App\Models\Rider;
use Workbench\App\Models\User;
use Workbench\App\Saddle\RiderResource;

// ---------------------------------------------------------------------------
// Helper — identical contract to ranchWithMember() in TenantResolutionTest.
// Pest functions are file-scoped; we define our own copy here so we don't
// depend on load order across test files.
// ---------------------------------------------------------------------------
function makeRanchWithMember(string $name, User $member): Ranch
{
    $ranch = Ranch::factory()->create(['name' => $name]);
    $ranch->users()->attach($member);

    return $ranch;
}

// ---------------------------------------------------------------------------
// 1. Foreign record edit GET → 404
// ---------------------------------------------------------------------------
it('returns 404 for a GET edit request targeting a foreign ranch horse', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    $foreign = Horse::factory()->create(['name' => 'Bandit', 'ranch_id' => $ranchB->id]);

    $this->get("/admin/{$ranchA->getRouteKey()}/resources/horses/{$foreign->id}/edit")
        ->assertNotFound();
});

// ---------------------------------------------------------------------------
// 2. Foreign record update PUT → 404, DB unchanged
// ---------------------------------------------------------------------------
it('returns 404 for a PUT update to a foreign ranch horse and leaves DB unchanged', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    $foreign = Horse::factory()->create(['name' => 'Smuggler', 'ranch_id' => $ranchB->id]);

    $this->put("/admin/{$ranchA->getRouteKey()}/resources/horses/{$foreign->id}", [
        'name' => 'Hijacked',
    ])->assertNotFound();

    expect($foreign->fresh()->name)->toBe('Smuggler');
});

// ---------------------------------------------------------------------------
// 3. Foreign record destroy DELETE → 404, record still exists
// ---------------------------------------------------------------------------
it('returns 404 for a DELETE destroy on a foreign ranch horse and the record survives', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    $foreign = Horse::factory()->create(['name' => 'Ghost', 'ranch_id' => $ranchB->id]);

    $this->delete("/admin/{$ranchA->getRouteKey()}/resources/horses/{$foreign->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('horses', ['id' => $foreign->id, 'name' => 'Ghost']);
});

// ---------------------------------------------------------------------------
// 4. Store stamps the current tenant — smuggled ranch_id is overwritten
// ---------------------------------------------------------------------------
it('stamps the current ranch on store even when the payload smuggles a foreign ranch_id', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    // The form only declares 'name', but we slip ranch_id into the raw POST.
    // The store controller fills validated fields first, then forcefully
    // associates the current tenant — the stamp must win.
    $this->post("/admin/{$ranchA->getRouteKey()}/resources/horses", [
        'name' => 'Stampede',
        'ranch_id' => $ranchB->id, // smuggled FK — must be ignored
    ])->assertRedirect();

    $created = Horse::where('name', 'Stampede')->firstOrFail();
    expect($created->ranch_id)->toBe($ranchA->id);
});

// ---------------------------------------------------------------------------
// 5. Search isolation — ranch B's horse is invisible to an A-member search
// ---------------------------------------------------------------------------
it('hides ranch B horses from a search by a ranch A member', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    Horse::factory()->create(['name' => 'Cisco', 'ranch_id' => $ranchA->id]);
    Horse::factory()->create(['name' => 'Bandit', 'ranch_id' => $ranchB->id]);

    $this->get("/admin/{$ranchA->getRouteKey()}/resources/horses?search=Bandit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Index')
            ->count('rows.data', 0)
        );
});

// ---------------------------------------------------------------------------
// 6. Filter isolation — ranch B's saddled horse is invisible to A's filter
// ---------------------------------------------------------------------------
it('hides ranch B saddled horses from a ranch A member using is_saddled filter', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    // Ranch A has no saddled horses; ranch B has the only saddled one.
    Horse::factory()->create(['name' => 'Unbridled', 'ranch_id' => $ranchA->id, 'is_saddled' => false]);
    Horse::factory()->create(['name' => 'Saddled', 'ranch_id' => $ranchB->id, 'is_saddled' => true]);

    $this->get("/admin/{$ranchA->getRouteKey()}/resources/horses?filter[is_saddled]=1")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Index')
            ->count('rows.data', 0)
        );
});

// ---------------------------------------------------------------------------
// 7a. Scoped relation options: RiderResource $tenant = 'ranch' returns only
//     the current tenant's riders
// ---------------------------------------------------------------------------
it('returns only ranch A riders from the options endpoint when RiderResource is tenant-scoped', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    $riderA = Rider::factory()->create(['name' => 'Alice', 'ranch_id' => $ranchA->id]);
    Rider::factory()->create(['name' => 'Bob', 'ranch_id' => $ranchB->id]);

    RiderResource::$tenant = 'ranch';

    try {
        $response = $this->getJson("/admin/{$ranchA->getRouteKey()}/resources/horses/options/rider_id");

        $response->assertOk();

        $options = $response->json('options');
        $labels = array_column($options, 'label');

        expect($labels)->toContain('Alice')
            ->and($labels)->not->toContain('Bob');
    } finally {
        RiderResource::$tenant = null;
    }
});

// ---------------------------------------------------------------------------
// 7b. Unscoped relation options: without RiderResource $tenant, options are
//     global (documented lookup-table behavior)
// ---------------------------------------------------------------------------
it('returns riders from all ranches when RiderResource is not tenant-scoped', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    Rider::factory()->create(['name' => 'Alice', 'ranch_id' => $ranchA->id]);
    Rider::factory()->create(['name' => 'Bob', 'ranch_id' => $ranchB->id]);

    // Explicitly ensure RiderResource is NOT tenant-scoped for this test
    // (global lookup-table behavior — documented non-goal of scoping).
    RiderResource::$tenant = null;

    $response = $this->getJson("/admin/{$ranchA->getRouteKey()}/resources/horses/options/rider_id");

    $response->assertOk();

    $options = $response->json('options');
    $labels = array_column($options, 'label');

    expect($labels)->toContain('Alice')
        ->and($labels)->toContain('Bob');
});

// ---------------------------------------------------------------------------
// 7c. Cross-tenant FK on write: a ranch A member cannot POST a horse whose
//     rider_id belongs to ranch B when RiderResource is tenant-scoped.
//     Mirrors 7a — RiderResource::$tenant = 'ranch' with try/finally restore.
// ---------------------------------------------------------------------------
it('rejects a foreign ranch rider_id on store when RiderResource is tenant-scoped', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    $foreignRider = Rider::factory()->create(['name' => 'Bob', 'ranch_id' => $ranchB->id]);

    RiderResource::$tenant = 'ranch';

    try {
        $this->post("/admin/{$ranchA->getRouteKey()}/resources/horses", [
            'name' => 'Trespasser',
            'rider_id' => $foreignRider->id,
        ])->assertSessionHasErrors(['rider_id']);

        $this->assertDatabaseMissing('horses', ['name' => 'Trespasser']);
    } finally {
        RiderResource::$tenant = null;
    }
});

// ---------------------------------------------------------------------------
// 7d. Control: a ranch A member CAN POST a horse with a ranch A rider_id even
//     when RiderResource is tenant-scoped — the scoped exists rule passes.
// ---------------------------------------------------------------------------
it('accepts a same-ranch rider_id on store when RiderResource is tenant-scoped', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);

    $localRider = Rider::factory()->create(['name' => 'Alice', 'ranch_id' => $ranchA->id]);

    RiderResource::$tenant = 'ranch';

    try {
        $this->post("/admin/{$ranchA->getRouteKey()}/resources/horses", [
            'name' => 'Homebound',
            'rider_id' => $localRider->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('horses', [
            'name' => 'Homebound',
            'rider_id' => $localRider->id,
            'ranch_id' => $ranchA->id,
        ]);
    } finally {
        RiderResource::$tenant = null;
    }
});

// ---------------------------------------------------------------------------
// 8. Tenant switching respects membership end to end
// ---------------------------------------------------------------------------
it('shows only the correct ranch horses when the same user switches tenant context', function () {
    $user = $this->actingAsUser();
    $ranchA = makeRanchWithMember('Alpha Ranch', $user);
    $ranchB = makeRanchWithMember('Beta Ranch', $user); // same user, second membership

    Horse::factory()->create(['name' => 'Cisco', 'ranch_id' => $ranchA->id]);
    Horse::factory()->create(['name' => 'Bandit', 'ranch_id' => $ranchB->id]);

    // Viewing from ranch B's context must show only Bandit.
    $this->get("/admin/{$ranchB->getRouteKey()}/resources/horses")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Index')
            ->count('rows.data', 1)
            ->where('rows.data.0.cells.name', 'Bandit')
        );
});
