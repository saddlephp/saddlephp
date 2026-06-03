<?php

declare(strict_types=1);

use RodeoPHP\Fields\Text;
use RodeoPHP\Fields\Toggle;
use RodeoPHP\Forms\Form;
use Workbench\App\Models\Horse;

function horseForm(): Form
{
    return Form::make()->schema([
        Text::make('name')->required(),
        Toggle::make('is_saddled'),
    ]);
}

it('aggregates rules keyed by field name', function () {
    expect(horseForm()->rules())->toBe([
        'name' => ['required', 'string'],
        'is_saddled' => ['nullable', 'boolean'],
    ]);
});

it('fills a record from validated data, skipping absent keys', function () {
    $horse = Horse::factory()->create(['name' => 'Cisco', 'is_saddled' => true]);

    horseForm()->fill($horse, ['name' => 'Dakota']);

    expect($horse->name)->toBe('Dakota')
        ->and($horse->is_saddled)->toBeTrue();
});

it('serializes fields for the frontend, resolving record values on edit', function () {
    $horse = Horse::factory()->create(['name' => 'Cisco']);

    $payload = horseForm()->toInertia($horse);

    expect($payload)->toHaveCount(2)
        ->and($payload[0]['name'])->toBe('name')
        ->and($payload[0]['value'])->toBe('Cisco');
});
