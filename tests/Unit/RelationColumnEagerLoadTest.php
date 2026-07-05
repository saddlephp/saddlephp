<?php

declare(strict_types=1);

use SaddlePHP\Http\Controllers\Controller;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;

/** Expose the protected root extractor for testing. */
function eagerLoadHarness(): object
{
    return new class extends Controller
    {
        public function roots(Table $table): array
        {
            return $this->relationColumnRoots($table);
        }
    };
}

it('derives eager-load roots from dot-path columns', function () {
    $table = Table::make()->columns([
        TextColumn::make('name'),
        TextColumn::make('rider.name'),
        TextColumn::make('rider.ranch.city'),
    ]);

    expect(eagerLoadHarness()->roots($table))->toBe(['rider', 'rider.ranch']);
});

it('deduplicates multiple columns on the same relation', function () {
    $table = Table::make()->columns([
        TextColumn::make('rider.name'),
        TextColumn::make('rider.email'),
    ]);

    expect(eagerLoadHarness()->roots($table))->toBe(['rider']);
});

it('returns no roots when there are no relation columns', function () {
    $table = Table::make()->columns([TextColumn::make('name'), TextColumn::make('breed')]);

    expect(eagerLoadHarness()->roots($table))->toBe([]);
});
