<?php

declare(strict_types=1);

use SaddlePHP\Tables\Columns\BadgeColumn;
use SaddlePHP\Tables\Columns\TextColumn;
use Workbench\App\Models\Horse;

it('uses an explicit label over the derived headline', function () {
    expect(TextColumn::make('created_at')->label('Joined')->toArray()['label'])->toBe('Joined');
});

it('defaults a badge column to an empty color map', function () {
    expect(BadgeColumn::make('breed')->toArray())
        ->toHaveKey('colors')
        ->and(BadgeColumn::make('breed')->toArray()['colors'])->toBe([]);
});

it('leaves non-date values untouched even when a date format is set', function () {
    $horse = Horse::factory()->create(['name' => 'Cisco']);

    // A string attribute is not a DateTimeInterface, so the format is a no-op.
    expect(TextColumn::make('name')->date('M j, Y')->resolve($horse))->toBe('Cisco');
});

it('date() with no argument formats with the default Y-m-d H:i', function () {
    $horse = Horse::factory()->create();

    expect(TextColumn::make('created_at')->date()->resolve($horse))
        ->toBe($horse->created_at->format('Y-m-d H:i'));
});
