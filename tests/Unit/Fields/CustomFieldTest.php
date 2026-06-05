<?php

declare(strict_types=1);

use SaddlePHP\Fields\CustomField;
use Workbench\App\Models\Horse;

it('serializes its element tag', function () {
    $payload = CustomField::make('mood')->tag('mood-picker')->toArray();

    expect($payload['component'])->toBe('custom-field')
        ->and($payload['tag'])->toBe('mood-picker')
        ->and($payload['name'])->toBe('mood');
});

it('refuses to serialize without a tag', function () {
    CustomField::make('mood')->toArray();
})->throws(LogicException::class);

it('keeps the standard field behaviors', function () {
    $field = CustomField::make('notes')->tag('mood-picker')->required()->rules('max:32');

    $horse = new Horse;
    $field->fill($horse, 'stoic');

    expect($field->getRules())->toBe(['required', 'max:32'])
        ->and($horse->notes)->toBe('stoic');
});

it('embeds the record value on the edit payload', function () {
    $horse = Horse::factory()->create(['notes' => 'stoic']);

    expect(CustomField::make('notes')->tag('mood-picker')->toArray($horse)['value'])->toBe('stoic');
});
