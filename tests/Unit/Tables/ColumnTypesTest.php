<?php

declare(strict_types=1);

use SaddlePHP\Tables\Columns\BadgeColumn;
use SaddlePHP\Tables\Columns\BooleanColumn;
use SaddlePHP\Tables\Columns\TextColumn;
use Workbench\App\Models\Horse;

it('badge serializes its type and color map', function () {
    $payload = BadgeColumn::make('breed')
        ->colors(['quarter' => 'accent', 'mustang' => 'ink'])
        ->toArray();

    expect($payload['type'])->toBe('badge')
        ->and($payload['colors'])->toBe(['quarter' => 'accent', 'mustang' => 'ink']);
});

it('boolean serializes its type and resolves to a real bool', function () {
    $horse = Horse::factory()->create(['is_saddled' => true]);

    $column = BooleanColumn::make('is_saddled');

    expect($column->toArray()['type'])->toBe('boolean')
        ->and($column->resolve($horse))->toBeTrue();

    $horse->is_saddled = false;
    expect($column->resolve($horse))->toBeFalse();

    // the cast must coerce non-boolean attributes to real bools
    $horse->age = 7;
    expect(BooleanColumn::make('age')->resolve($horse))->toBeTrue();

    $horse->age = 0;
    expect(BooleanColumn::make('age')->resolve($horse))->toBeFalse();
});

it('refuses to make a relation column sortable', function () {
    TextColumn::make('rider.name')->sortable();
})->throws(LogicException::class, 'rider.name');

it('refuses to make a relation column searchable', function () {
    TextColumn::make('rider.name')->searchable();
})->throws(LogicException::class, 'rider.name');

it('allows plain columns to be sortable and searchable', function () {
    $column = TextColumn::make('name')->sortable()->searchable();

    expect($column->isSortable())->toBeTrue()
        ->and($column->isSearchable())->toBeTrue();
});

it('never throws when a relation column disables sorting or searching', function () {
    $column = TextColumn::make('rider.name')->sortable(false)->searchable(false);

    expect($column->isSortable())->toBeFalse()
        ->and($column->isSearchable())->toBeFalse();
});
