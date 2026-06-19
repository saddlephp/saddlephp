<?php

declare(strict_types=1);

use Workbench\App\Models\Ranch;

it('redirects when the tenancy gate denies access', function () {
    config()->set('saddle.tenancy.gate', BillingGate::class);
    $user = $this->actingAsUser();
    $ranch = Ranch::factory()->create(['name' => 'Alpha']);
    $ranch->users()->attach($user);

    $this->get("/admin/{$ranch->getRouteKey()}")->assertRedirect('/billing');
});

it('proceeds when no gate is configured', function () {
    config()->set('saddle.tenancy.gate', null);
    $user = $this->actingAsUser();
    $ranch = Ranch::factory()->create(['name' => 'Alpha']);
    $ranch->users()->attach($user);

    $this->get("/admin/{$ranch->getRouteKey()}")->assertOk();
});

class BillingGate
{
    public function __invoke($request, $tenant)
    {
        return redirect('/billing');
    }
}
