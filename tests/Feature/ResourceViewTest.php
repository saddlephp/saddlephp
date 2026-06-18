<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;
use Workbench\App\Models\User;

it('renders the view page with display values', function () {
    $this->actingAsUser();
    $horse = Horse::factory()->create(['name' => 'Cisco', 'is_saddled' => true]);

    $this->get("/admin/resources/horses/{$horse->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Show')
            ->where('record.id', $horse->id)
            ->where('record.title', 'Cisco')
            ->where('record.can.update', true)
            ->where('fields', fn ($fields) => findField(collect($fields)->all(), 'name')['display'] === 'Cisco')
            ->where('fields', fn ($fields) => findField(collect($fields)->all(), 'is_saddled')['type'] === 'boolean')
        );
});

it('404s for a missing record', function () {
    $this->actingAsUser();
    $this->get('/admin/resources/horses/999')->assertNotFound();
});

it('403s when the view ability is denied by policy', function () {
    Gate::policy(Horse::class, DenyHorseViewPolicy::class);
    $this->actingAsUser();
    $horse = Horse::factory()->create();

    $this->get("/admin/resources/horses/{$horse->id}")->assertForbidden();
});

class DenyHorseViewPolicy
{
    public function view(User $user, Horse $horse): bool
    {
        return false;
    }
}
