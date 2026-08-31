<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use SaddlePHP\Fields\BelongsTo;
use SaddlePHP\Tests\Fixtures\DenyViewAnyPolicy;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Rider;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->actingAsUser();
    Rider::factory()->create(['name' => 'Billie']);
    Rider::factory()->create(['name' => 'Amos']);
});

it('refuses relation options when the related resource denies viewAny', function () {
    Gate::policy(Rider::class, DenyViewAnyPolicy::class);

    $this->getJson('/admin/resources/horses/options/rider_id')
        ->assertForbidden();
});

it('refuses a searched relation options walk when the related resource denies viewAny', function () {
    Gate::policy(Rider::class, DenyViewAnyPolicy::class);

    // Iterating prefixes is how the whole related table gets walked 100 rows at
    // a time, so the search path has to be closed as firmly as the bare one.
    $this->getJson('/admin/resources/horses/options/rider_id?search=B')
        ->assertForbidden();
});

it('still serves relation options when the related resource allows viewAny', function () {
    $this->getJson('/admin/resources/horses/options/rider_id')
        ->assertOk()
        ->assertJsonCount(2, 'options');
});

it('does not inline related options into the field payload when viewAny is denied', function () {
    Gate::policy(Rider::class, DenyViewAnyPolicy::class);

    // A non-searchable BelongsTo inlines options() straight into the form
    // payload via meta(), so the same rows leak without the options endpoint
    // being touched at all.
    $field = BelongsTo::make('rider');
    $field->bound(new Horse);

    expect($field->toArray()['options'])->toBe([]);
});

it('still inlines related options when viewAny is allowed', function () {
    $field = BelongsTo::make('rider');
    $field->bound(new Horse);

    expect($field->toArray()['options'])->toHaveCount(2);
});

it('opts a lookup table out of the gate with publicOptions()', function () {
    Gate::policy(Rider::class, DenyViewAnyPolicy::class);

    $field = BelongsTo::make('rider')->publicOptions();
    $field->bound(new Horse);

    expect($field->toArray()['options'])->toHaveCount(2);
});

it('fails closed when the related model has no registered resource', function () {
    // User is a real model with no resource of its own. Without an owning
    // resource there is no viewAny to consult, so the safe answer is nothing.
    $field = BelongsTo::make('user');
    $field->bound(new class extends Horse
    {
        public function user()
        {
            return $this->belongsTo(User::class, 'rider_id');
        }
    });

    expect($field->searchOptions(''))->toBe([]);
});
