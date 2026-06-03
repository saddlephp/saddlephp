<?php

declare(strict_types=1);

use Illuminate\Validation\Rules\In;
use RodeoPHP\Fields\Select;
use RodeoPHP\Fields\Textarea;
use RodeoPHP\Fields\Toggle;
use Workbench\App\Models\Horse;

enum Breed: string
{
    case Quarter = 'quarter';
    case Mustang = 'mustang';
}

it('textarea exposes rows meta and string rule', function () {
    $payload = Textarea::make('notes')->rows(6)->toArray();

    expect($payload['component'])->toBe('textarea-field')
        ->and($payload['rows'])->toBe(6)
        ->and(Textarea::make('notes')->getRules())->toBe(['nullable', 'string']);
});

it('select normalizes assoc-array options', function () {
    $payload = Select::make('breed')->options(['quarter' => 'Quarter Horse', 'mustang' => 'Mustang'])->toArray();

    expect($payload['component'])->toBe('select-field')
        ->and($payload['options'])->toBe([
            ['value' => 'quarter', 'label' => 'Quarter Horse'],
            ['value' => 'mustang', 'label' => 'Mustang'],
        ]);
});

it('select accepts a backed enum class and gains an in rule', function () {
    $field = Select::make('breed')->options(Breed::class);

    expect($field->toArray()['options'])->toBe([
        ['value' => 'quarter', 'label' => 'Quarter'],
        ['value' => 'mustang', 'label' => 'Mustang'],
    ]);

    $rules = $field->getRules();
    expect($rules[0])->toBe('nullable')
        ->and($rules[1])->toBeInstanceOf(In::class);
});

it('toggle is always boolean, fills a cast bool and defaults false', function () {
    $field = Toggle::make('is_saddled');

    expect($field->getRules())->toBe(['nullable', 'boolean'])
        ->and($field->toArray()['value'])->toBeFalse();

    $horse = Horse::factory()->create(['is_saddled' => false]);
    $field->fill($horse, '1');
    expect($horse->is_saddled)->toBeTrue();

    $field->fill($horse, null);
    expect($horse->is_saddled)->toBeFalse();
});
