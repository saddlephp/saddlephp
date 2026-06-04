<?php

declare(strict_types=1);

use SaddlePHP\Fields\Date;
use SaddlePHP\Fields\Number;
use Workbench\App\Models\Horse;

it('number builds numeric rules with bounds and integer mode', function () {
    expect(Number::make('age')->integer()->min(0)->max(50)->getRules())
        ->toBe(['nullable', 'integer', 'min:0', 'max:50']);

    expect(Number::make('weight')->min(0.5)->getRules())
        ->toBe(['nullable', 'numeric', 'min:0.5']);
});

it('number serializes its input meta', function () {
    $payload = Number::make('age')->min(0)->max(50)->step(1)->toArray();

    expect($payload['component'])->toBe('number-field')
        ->and($payload['type'])->toBe('number')
        ->and($payload['min'])->toEqual(0)
        ->and($payload['max'])->toEqual(50)
        ->and($payload['step'])->toEqual(1);
});

it('date validates as a date and resolves casts to Y-m-d', function () {
    $field = Date::make('foaled_on');

    expect($field->getRules())->toBe(['nullable', 'date'])
        ->and($field->toArray()['component'])->toBe('date-field');

    $horse = Horse::factory()->create(['foaled_on' => '2019-05-01']);
    expect($field->resolve($horse))->toBe('2019-05-01');
});
