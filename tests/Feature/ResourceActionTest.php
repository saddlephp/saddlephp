<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use SaddlePHP\Saddle;
use SaddlePHP\Tests\Fixtures\ActionHorseResource;
use SaddlePHP\Tests\Fixtures\DenyViewAnyPolicy;
use SaddlePHP\Tests\Fixtures\LockedDownHorsePolicy;
use Workbench\App\Models\Horse;

beforeEach(function () {
    app(Saddle::class)->register([ActionHorseResource::class]);
    $this->actingAsUser();
});

it('runs a row action, mutates the record, and flashes its success message', function () {
    $horse = Horse::factory()->create(['name' => 'Cisco']);

    $this->from('/admin/resources/action-horses')
        ->post('/admin/resources/action-horses/actions/rename', ['record' => $horse->id])
        ->assertRedirect('/admin/resources/action-horses')
        ->assertSessionHas('success', 'Renamed.');

    expect($horse->fresh()->name)->toBe('Renamed');
});

it('404s an unknown action name', function () {
    $horse = Horse::factory()->create();

    $this->post('/admin/resources/action-horses/actions/nope', ['record' => $horse->id])
        ->assertNotFound();
});

it('404s when the record id does not exist', function () {
    $this->post('/admin/resources/action-horses/actions/rename', ['record' => 999999])
        ->assertNotFound();
});

it('422s when the record id is missing', function () {
    $this->post('/admin/resources/action-horses/actions/rename', [])
        ->assertSessionHasErrors('record');
});

it('403s a guarded action when the policy denies and leaves the record untouched', function () {
    Gate::policy(Horse::class, LockedDownHorsePolicy::class);
    $horse = Horse::factory()->create(['name' => 'Cisco']);

    $this->post('/admin/resources/action-horses/actions/guarded', ['record' => $horse->id])
        ->assertForbidden();

    expect($horse->fresh()->name)->toBe('Cisco');
});

it('runs a guarded action when no policy is registered (fail-open default)', function () {
    $horse = Horse::factory()->create(['name' => 'Cisco']);

    $this->post('/admin/resources/action-horses/actions/guarded', ['record' => $horse->id])
        ->assertRedirect();

    expect($horse->fresh()->name)->toBe('Guarded');
});

it('runs a bulk action against only the selected records', function () {
    $a = Horse::factory()->create(['breed' => 'mustang']);
    $b = Horse::factory()->create(['breed' => 'mustang']);
    $c = Horse::factory()->create(['breed' => 'mustang']);

    $this->from('/admin/resources/action-horses')
        ->post('/admin/resources/action-horses/actions/brand', ['records' => [$a->id, $b->id]])
        ->assertRedirect('/admin/resources/action-horses')
        ->assertSessionHas('success', 'Done.');

    expect($a->fresh()->breed)->toBe('branded')
        ->and($b->fresh()->breed)->toBe('branded')
        ->and($c->fresh()->breed)->toBe('mustang');
});

it('runs the bulk delete preset against the selected records', function () {
    $a = Horse::factory()->create();
    $b = Horse::factory()->create();
    $c = Horse::factory()->create();

    $this->post('/admin/resources/action-horses/actions/delete', ['records' => [$a->id, $b->id]])
        ->assertRedirect();

    expect(Horse::query()->find($a->id))->toBeNull()
        ->and(Horse::query()->find($b->id))->toBeNull()
        ->and(Horse::query()->find($c->id))->not->toBeNull();
});

it('422s a bulk action over the 100-id cap', function () {
    $ids = range(1, 101);

    $this->post('/admin/resources/action-horses/actions/brand', ['records' => $ids])
        ->assertSessionHasErrors('records');
});

it('404s a bulk action when any id is absent and mutates nothing (all-or-nothing)', function () {
    $valid = Horse::factory()->create(['breed' => 'mustang']);

    $this->post('/admin/resources/action-horses/actions/brand', ['records' => [$valid->id, 999999]])
        ->assertNotFound();

    expect($valid->fresh()->breed)->toBe('mustang');
});

it('dedupes duplicate ids in a bulk action and does not false-404', function () {
    $horse = Horse::factory()->create(['breed' => 'mustang']);

    $this->post('/admin/resources/action-horses/actions/brand', ['records' => [$horse->id, $horse->id]])
        ->assertRedirect();

    expect($horse->fresh()->breed)->toBe('branded');
});

it('throws a LogicException for a declared action with no handler', function () {
    $horse = Horse::factory()->create();

    $this->withoutExceptionHandling();

    expect(fn () => $this->post('/admin/resources/action-horses/actions/hollow', ['record' => $horse->id]))
        ->toThrow(LogicException::class);
});

it('denies the endpoint outright when viewAny fails', function () {
    Gate::policy(Horse::class, DenyViewAnyPolicy::class);
    $this->actingAsUser();
    $horse = Horse::factory()->create(['name' => 'Cisco']);

    $this->post('/admin/resources/action-horses/actions/rename', ['record' => $horse->id])
        ->assertForbidden();

    expect($horse->fresh()->name)->toBe('Cisco');
});
