<?php

declare(strict_types=1);

use RodeoPHP\Tables\Columns\TextColumn;
use RodeoPHP\Tables\Table;
use Workbench\App\Models\Horse;

function horseTable(): Table
{
    return Table::make()->columns([
        TextColumn::make('name')->sortable()->searchable(),
        TextColumn::make('breed')->sortable(),
        TextColumn::make('created_at'),
    ]);
}

it('reports sortable and searchable column names', function () {
    expect(horseTable()->sortableColumns())->toBe(['name', 'breed'])
        ->and(horseTable()->searchableColumns())->toBe(['name']);
});

it('serializes column metadata', function () {
    expect(horseTable()->toInertia())->toBe([
        ['name' => 'name', 'label' => 'Name', 'sortable' => true],
        ['name' => 'breed', 'label' => 'Breed', 'sortable' => true],
        ['name' => 'created_at', 'label' => 'Created At', 'sortable' => false],
    ]);
});

it('resolves cell values, formatting dates', function () {
    $horse = Horse::factory()->create(['name' => 'Cisco']);

    $name = TextColumn::make('name')->resolve($horse);
    $created = TextColumn::make('created_at')->resolve($horse);

    expect($name)->toBe('Cisco')
        ->and($created)->toBe($horse->created_at->format('Y-m-d H:i'));
});
