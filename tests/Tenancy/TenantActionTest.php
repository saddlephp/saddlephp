<?php

declare(strict_types=1);

use SaddlePHP\Saddle;
use SaddlePHP\Tests\Fixtures\ActionHorseResource;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Ranch;
use Workbench\App\Models\User;

// ---------------------------------------------------------------------------
// Helper — file-scoped copy matching the established ranchWithMember pattern.
// ---------------------------------------------------------------------------
function ranchWithActionMember(string $name, User $member): Ranch
{
    $ranch = Ranch::factory()->create(['name' => $name]);
    $ranch->users()->attach($member);

    return $ranch;
}

// ---------------------------------------------------------------------------
// Register ActionHorseResource and set its $tenant static for every test,
// restoring both in tearDown via try/finally so statics never leak.
// TenancyTestCase (auto-used for this directory) already handles HorseResource.
// ---------------------------------------------------------------------------
beforeEach(function () {
    app(Saddle::class)->register([ActionHorseResource::class]);
    ActionHorseResource::$tenant = 'ranch';
});

afterEach(function () {
    ActionHorseResource::$tenant = null;
});

// ---------------------------------------------------------------------------
// 1. Row action against a foreign (ranch B) horse → 404; horse name unchanged
// ---------------------------------------------------------------------------
it('returns 404 when a row action targets a foreign ranch horse and leaves it untouched', function () {
    $user = $this->actingAsUser();
    $ranchA = ranchWithActionMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    $foreign = Horse::factory()->create(['name' => 'Outlaw', 'ranch_id' => $ranchB->id]);

    $this->post(
        "/admin/{$ranchA->getRouteKey()}/resources/action-horses/actions/rename",
        ['record' => $foreign->id],
    )->assertNotFound();

    expect($foreign->fresh()->name)->toBe('Outlaw');
});

// ---------------------------------------------------------------------------
// 2. Row action against an own-ranch horse → 200-redirect + name mutated
//    (control: proves the scoped path actually works under tenancy)
// ---------------------------------------------------------------------------
it('runs a row action against an own-ranch horse, mutates it, and redirects', function () {
    $user = $this->actingAsUser();
    $ranchA = ranchWithActionMember('Alpha Ranch', $user);

    $own = Horse::factory()->create(['name' => 'Cisco', 'ranch_id' => $ranchA->id]);

    $this->from("/admin/{$ranchA->getRouteKey()}/resources/action-horses")
        ->post(
            "/admin/{$ranchA->getRouteKey()}/resources/action-horses/actions/rename",
            ['record' => $own->id],
        )->assertRedirect();

    expect($own->fresh()->name)->toBe('Renamed');
});

// ---------------------------------------------------------------------------
// 3. Bulk 'brand' over [ownId, foreignId] → 404; BOTH horses untouched
//    (all-or-nothing: the foreign id is absent from the scoped query)
// ---------------------------------------------------------------------------
it('returns 404 on bulk brand when the selection contains a foreign ranch horse and mutates nothing', function () {
    $user = $this->actingAsUser();
    $ranchA = ranchWithActionMember('Alpha Ranch', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta Ranch']);

    $own = Horse::factory()->create(['breed' => 'mustang', 'ranch_id' => $ranchA->id]);
    $foreign = Horse::factory()->create(['breed' => 'mustang', 'ranch_id' => $ranchB->id]);

    $this->post(
        "/admin/{$ranchA->getRouteKey()}/resources/action-horses/actions/brand",
        ['records' => [$own->id, $foreign->id]],
    )->assertNotFound();

    expect($own->fresh()->breed)->toBe('mustang')
        ->and($foreign->fresh()->breed)->toBe('mustang');
});

// ---------------------------------------------------------------------------
// 4. Bulk 'brand' over two own-ranch horses → both branded
//    (control: proves bulk works correctly inside tenant scope)
// ---------------------------------------------------------------------------
it('brands both own-ranch horses when bulk action targets only same-ranch records', function () {
    $user = $this->actingAsUser();
    $ranchA = ranchWithActionMember('Alpha Ranch', $user);

    $ownA = Horse::factory()->create(['breed' => 'mustang', 'ranch_id' => $ranchA->id]);
    $ownB = Horse::factory()->create(['breed' => 'mustang', 'ranch_id' => $ranchA->id]);

    $this->post(
        "/admin/{$ranchA->getRouteKey()}/resources/action-horses/actions/brand",
        ['records' => [$ownA->id, $ownB->id]],
    )->assertRedirect();

    expect($ownA->fresh()->breed)->toBe('branded')
        ->and($ownB->fresh()->breed)->toBe('branded');
});
