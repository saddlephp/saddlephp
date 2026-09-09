<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use SaddlePHP\Tables\Columns\BadgeColumn;
use SaddlePHP\Tables\Columns\BooleanColumn;
use SaddlePHP\Tables\Columns\TextColumn;
use Workbench\App\Models\Horse;

it('resolves the raw value when no formatter is registered', function () {
    $horse = Horse::factory()->create(['name' => 'Comet', 'age' => null]);

    expect(TextColumn::make('name')->resolve($horse))->toBe('Comet')
        ->and(TextColumn::make('age')->resolve($horse))->toBeNull();
});

/**
 * The case this exists for: a panel where "a null is not a zero" is a stated
 * rule. A StatWidget can honour it because value() returns a string; a table
 * column could not, so the same figure was an em dash on the dashboard and an
 * empty gap in the table beneath it.
 */
it('lets a formatter render a null as something other than an empty cell', function () {
    $horse = Horse::factory()->create(['age' => null]);

    $column = TextColumn::make('age')->formatUsing(fn (mixed $value) => $value ?? '—');

    expect($column->resolve($horse))->toBe('—');
});

it('hands the formatter the record as well as the value', function () {
    $horse = Horse::factory()->create(['name' => 'Comet', 'age' => 7]);

    $column = TextColumn::make('age')->formatUsing(
        fn (mixed $value, Model $record) => "{$record->name} is {$value}"
    );

    expect($column->resolve($horse))->toBe('Comet is 7');
});

/**
 * The whole reason the hook belongs on the column rather than on a model
 * accessor: an accessor cannot be ->sortable(), because sorting happens in the
 * database and the accessor does not exist there. Formatting runs after the
 * value is resolved, so both still refer to the real column.
 */
it('leaves a formatted column sortable and searchable', function () {
    $column = TextColumn::make('name')
        ->formatUsing(fn (mixed $value) => strtoupper((string) $value))
        ->sortable()
        ->searchable();

    expect($column->isSortable())->toBeTrue()
        ->and($column->isSearchable())->toBeTrue();
});

it('formats badge and boolean columns too, so the hook means one thing everywhere', function () {
    $horse = Horse::factory()->create(['is_saddled' => false, 'breed' => null]);

    expect(BooleanColumn::make('is_saddled')->resolve($horse))->toBeFalse()
        ->and(BooleanColumn::make('is_saddled')->formatUsing(fn (mixed $v) => ! $v)->resolve($horse))->toBeTrue()
        ->and(BadgeColumn::make('breed')->formatUsing(fn (mixed $v) => $v ?? 'unknown')->resolve($horse))->toBe('unknown');
});

it('hands a TextColumn formatter the raw date, leaving date() to format what is still a date', function () {
    $horse = Horse::factory()->create();

    $formatted = TextColumn::make('created_at')
        ->date('Y-m-d')
        ->formatUsing(fn (mixed $value) => $value?->format('D jS M Y'));

    $untouched = TextColumn::make('created_at')
        ->date('Y-m-d')
        ->formatUsing(fn (mixed $value) => $value);

    expect($formatted->resolve($horse))->toBe($horse->created_at->format('D jS M Y'))
        ->and($untouched->resolve($horse))->toBe($horse->created_at->format('Y-m-d'));
});

it('leaves the serialized column payload untouched, because formatting is not metadata', function () {
    $plain = TextColumn::make('age')->toArray();
    $formatted = TextColumn::make('age')->formatUsing(fn (mixed $value) => $value ?? '—')->toArray();

    expect($formatted)->toBe($plain);
});
