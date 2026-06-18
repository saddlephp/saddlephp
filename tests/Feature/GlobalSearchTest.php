<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Rider;
use Workbench\App\Models\User;
use Workbench\App\Saddle\HorseResource;

it('groups search results by resource and links to the view page', function () {
    $this->actingAsUser();
    $horse = Horse::factory()->create(['name' => 'Comanche']);
    Rider::factory()->create(['name' => 'Comstock']);
    Horse::factory()->create(['name' => 'Dakota']);

    $groups = collect($this->getJson('/admin/resources/search?q=Com')->assertOk()->json('groups'));
    $horses = $groups->firstWhere('uriKey', 'horses');

    expect($horses)->not->toBeNull()
        ->and($groups->firstWhere('uriKey', 'riders'))->not->toBeNull()
        ->and(collect($horses['results'])->pluck('title')->all())->toBe(['Comanche'])
        ->and($horses['results'][0]['url'])->toBe("/admin/resources/horses/{$horse->id}");
});

it('returns no groups for an empty query', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Comanche']);

    $this->getJson('/admin/resources/search?q=')
        ->assertOk()
        ->assertExactJson(['query' => '', 'groups' => []]);
});

it('excludes a resource that opts out of global search', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Comanche']);

    HorseResource::$globalSearch = false;

    try {
        $groups = collect($this->getJson('/admin/resources/search?q=Com')->json('groups'));
        expect($groups->firstWhere('uriKey', 'horses'))->toBeNull();
    } finally {
        HorseResource::$globalSearch = true;
    }
});

it('hides resources the user cannot view', function () {
    $this->actingAsUser(['is_admin' => false]);
    Gate::policy(Horse::class, DenyHorseViewAnyPolicy::class);
    Horse::factory()->create(['name' => 'Comanche']);

    $groups = collect($this->getJson('/admin/resources/search?q=Com')->json('groups'));
    expect($groups->firstWhere('uriKey', 'horses'))->toBeNull();
});

class DenyHorseViewAnyPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }
}
