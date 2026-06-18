<?php

declare(strict_types=1);

use Workbench\App\Models\Horse;
use Workbench\App\Models\Ranch;
use Workbench\App\Models\User;

function ranchWithSearchMember(string $name, User $member): Ranch
{
    $ranch = Ranch::factory()->create(['name' => $name]);
    $ranch->users()->attach($member);

    return $ranch;
}

it('returns only the current tenant\'s records from global search', function () {
    $user = $this->actingAsUser();
    $ranchA = ranchWithSearchMember('Alpha', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta']);

    Horse::factory()->create(['name' => 'Comanche', 'ranch_id' => $ranchA->id]);
    Horse::factory()->create(['name' => 'Comet', 'ranch_id' => $ranchB->id]);

    $groups = collect($this->getJson("/admin/{$ranchA->getRouteKey()}/resources/search?q=Com")->json('groups'));
    $titles = collect($groups->firstWhere('uriKey', 'horses')['results'] ?? [])->pluck('title');

    expect($titles)->toContain('Comanche')->and($titles)->not->toContain('Comet');
});
