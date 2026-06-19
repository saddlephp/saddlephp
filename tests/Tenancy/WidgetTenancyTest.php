<?php

declare(strict_types=1);

use SaddlePHP\Saddle;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Ranch;
use Workbench\App\Models\User;
use Workbench\App\Saddle\Widgets\TenantHorseCountWidget;

function ranchWithWidgetMember(string $name, User $member): Ranch
{
    $ranch = Ranch::factory()->create(['name' => $name]);
    $ranch->users()->attach($member);

    return $ranch;
}

it('scopes a tenant-aware widget to the bound tenant', function () {
    app(Saddle::class)->registerWidgets([TenantHorseCountWidget::class]);
    $user = $this->actingAsUser();
    $ranchA = ranchWithWidgetMember('Alpha', $user);
    $ranchB = Ranch::factory()->create(['name' => 'Beta']);
    Horse::factory()->create(['ranch_id' => $ranchA->id]);
    Horse::factory()->count(2)->create(['ranch_id' => $ranchB->id]);

    $this->get("/admin/{$ranchA->getRouteKey()}")
        ->assertOk()
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->where('widgets', function ($widgets) {
                $stat = collect($widgets)->firstWhere('label', 'Tenant horses');

                return $stat !== null && $stat['value'] === '1';
            })
        );
});
