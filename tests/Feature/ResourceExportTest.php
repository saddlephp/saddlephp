<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Workbench\App\Models\Horse;
use Workbench\App\Models\User;

it('exports the filtered records as CSV', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Cisco', 'breed' => 'quarter']);
    Horse::factory()->create(['name' => 'Scout', 'breed' => 'mustang']);

    $response = $this->get('/admin/resources/horses/export?filter[breed]=quarter');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');

    $csv = $response->streamedContent();
    expect($csv)->toContain('Name')        // a column label header
        ->and($csv)->toContain('Cisco')
        ->and($csv)->not->toContain('Scout'); // filtered out
});

it('neutralizes formula-injection values in exported cells', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => '=1+2', 'breed' => 'quarter']);

    $csv = $this->get('/admin/resources/horses/export')->streamedContent();

    // The dangerous cell is prefixed with a quote so spreadsheets treat it as
    // text; without the fix the cell would be a bare "=1+2".
    expect($csv)->toContain("'=1+2");
});

it('gates export behind viewAny', function () {
    $this->actingAsUser(['is_admin' => false]);
    Gate::policy(Horse::class, DenyExportViewAnyPolicy::class);

    $this->get('/admin/resources/horses/export')->assertForbidden();
});

class DenyExportViewAnyPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }
}
