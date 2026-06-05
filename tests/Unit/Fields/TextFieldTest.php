<?php

declare(strict_types=1);

use SaddlePHP\Fields\Text;
use Workbench\App\Models\Horse;

it('serializes to a payload with derived label and defaults', function () {
    $payload = Text::make('name')->required()->placeholder('e.g. Cisco')->toArray();

    expect($payload)->toMatchArray([
        'component' => 'text-field',
        'name' => 'name',
        'label' => 'Name',
        'required' => true,
        'placeholder' => 'e.g. Cisco',
        'helper' => null,
        'value' => null,
        'type' => 'text',
    ]);
});

it('builds validation rules from required, type and custom rules', function () {
    // The default max:65535 is appended before custom rules so a stricter
    // author-supplied max (120) still composes and wins for longer values.
    expect(Text::make('name')->required()->rules('max:120')->getRules())
        ->toBe(['required', 'string', 'max:65535', 'max:120']);

    expect(Text::make('email')->type('email')->getRules())
        ->toBe(['nullable', 'email', 'max:65535']);
});

it('bounds text length by default with max:65535', function () {
    expect(Text::make('name')->getRules())->toContain('max:65535');
});

it('resolves a value from a record and fills one back', function () {
    $horse = Horse::factory()->create(['name' => 'Cisco']);
    $field = Text::make('name');

    expect($field->resolve($horse))->toBe('Cisco');

    $field->fill($horse, 'Dakota');
    expect($horse->name)->toBe('Dakota');
});

it('uses the default value when no record is given', function () {
    expect(Text::make('breed')->default('mustang')->toArray()['value'])->toBe('mustang');
});

it('honors an explicit label', function () {
    expect(Text::make('is_saddled')->label('Saddled?')->toArray()['label'])->toBe('Saddled?');
});
