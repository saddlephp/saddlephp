<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use SaddlePHP\Http\Controllers\Controller;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Horse;

/** Expose the protected root extractor for testing. */
function eagerLoadHarness(): object
{
    return new class extends Controller
    {
        public function roots(Table $table, Model $model): array
        {
            return $this->relationColumnRoots($table, $model);
        }
    };
}

it('derives eager-load roots from dot-path relation columns', function () {
    $table = Table::make()->columns([
        TextColumn::make('name'),
        TextColumn::make('rider.name'),
        TextColumn::make('rider.ranch.city'),
    ]);

    // Horse has a rider() relation, so both rider roots are eager-loadable.
    expect(eagerLoadHarness()->roots($table, new Horse))->toBe(['rider', 'rider.ranch']);
});

it('deduplicates multiple columns on the same relation', function () {
    $table = Table::make()->columns([
        TextColumn::make('rider.name'),
        TextColumn::make('rider.email'),
    ]);

    expect(eagerLoadHarness()->roots($table, new Horse))->toBe(['rider']);
});

it('skips dot-path columns that are not relations (e.g. JSON casts)', function () {
    $table = Table::make()->columns([
        TextColumn::make('rider.name'),      // real relation -> kept
        TextColumn::make('settings.theme'),  // no settings() relation -> dropped
    ]);

    // A non-relation dot-path must not reach ->with(), which would throw.
    expect(eagerLoadHarness()->roots($table, new Horse))->toBe(['rider']);
});

it('returns no roots when there are no relation columns', function () {
    $table = Table::make()->columns([TextColumn::make('name'), TextColumn::make('breed')]);

    expect(eagerLoadHarness()->roots($table, new Horse))->toBe([]);
});
