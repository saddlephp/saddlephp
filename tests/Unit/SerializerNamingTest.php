<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use SaddlePHP\Fields\Text;
use SaddlePHP\Forms\Form;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Horse;

/**
 * `Field::toArray()` and `Column::toArray()` serialize a leaf, but the
 * containers named the same operation `toInertia()`. Calling `Form::toArray()`
 * on the reasonable assumption that it matched the others returned
 * "Call to undefined method" -- a two-minute papercut that every consumer
 * writing tests against a form pays once.
 *
 * Both names now work, and the aliases must stay exactly that: the same array,
 * not a second serializer that can drift.
 */
it('serializes a form under either name, identically', function () {
    $horse = Horse::factory()->create(['name' => 'Cisco']);
    $form = Form::make()->schema([Text::make('name'), Text::make('breed')]);

    expect($form->toArray())->toBe($form->toInertia())
        ->and($form->toArray($horse))->toBe($form->toInertia($horse))
        ->and($form->toArray($horse)[0]['value'])->toBe('Cisco');
});

it('serializes a table under either name, identically', function () {
    $table = Table::make()->columns([TextColumn::make('name')->sortable(), TextColumn::make('breed')]);
    $request = Request::create('/admin/resources/horses');

    expect($table->toArray())->toBe($table->toInertia())
        ->and($table->toArray($request))->toBe($table->toInertia($request))
        ->and($table->toArray())->toHaveCount(2);
});

it('honours the column gate through the alias, so it cannot become a bypass', function () {
    $table = Table::make()->columns([
        TextColumn::make('name'),
        TextColumn::make('notes')->canSee(fn () => false),
    ]);

    expect(collect($table->toArray())->pluck('name')->all())->toBe(['name']);
});

it('honours field visibility through the alias too', function () {
    $form = Form::make()->schema([
        Text::make('name'),
        Text::make('notes')->canSee(fn () => false),
    ]);

    expect(collect($form->toArray())->pluck('name')->all())->toBe(['name']);
});
