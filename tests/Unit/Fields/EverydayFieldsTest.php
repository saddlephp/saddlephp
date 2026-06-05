<?php

declare(strict_types=1);

use SaddlePHP\Fields\DateTime;
use SaddlePHP\Fields\Markdown;
use Workbench\App\Models\Horse;

// ---------------------------------------------------------------------------
// DateTime
// ---------------------------------------------------------------------------

it('datetime serializes the datetime-field component', function () {
    expect(DateTime::make('created_at')->toArray()['component'])->toBe('datetime-field');
});

it('datetime rules are nullable date', function () {
    expect(DateTime::make('created_at')->getRules())->toBe(['nullable', 'date']);
});

it('datetime resolve formats a DateTimeInterface to Y-m-dTH:i', function () {
    $horse = Horse::factory()->create();
    $field = DateTime::make('created_at');

    expect($field->resolve($horse))->toBe($horse->created_at->format('Y-m-d\TH:i'));
});

it('datetime resolve passes null through unchanged', function () {
    $horse = Horse::factory()->make(['foaled_on' => null]);

    // Use foaled_on (nullable date column) to exercise the null branch.
    // We unset created_at cast so the attribute is truly null.
    $horse->setRawAttributes(['created_at' => null], true);

    $field = DateTime::make('created_at');

    expect($field->resolve($horse))->toBeNull();
});

// ---------------------------------------------------------------------------
// Markdown
// ---------------------------------------------------------------------------

it('markdown serializes the markdown-field component', function () {
    expect(Markdown::make('notes')->toArray()['component'])->toBe('markdown-field');
});

it('markdown rules are nullable string max:65535', function () {
    expect(Markdown::make('notes')->getRules())->toBe(['nullable', 'string', 'max:65535']);
});

it('markdown required() composes to required string max:65535', function () {
    expect(Markdown::make('notes')->required()->getRules())->toBe(['required', 'string', 'max:65535']);
});

it('markdown fill round-trips on horse notes', function () {
    $horse = Horse::factory()->create(['notes' => 'old value']);
    $field = Markdown::make('notes');

    $field->fill($horse, '**new** value');

    expect($horse->notes)->toBe('**new** value');
});
