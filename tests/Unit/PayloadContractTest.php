<?php

declare(strict_types=1);

// The array shapes these elements serialize to ARE the framework's public
// contract with the Vue frontend and with plugin authors. Pin the exact key
// set (including conditional keys) so a rename or reorder can't silently break
// every downstream consumer. Values are covered by the per-element tests; this
// file locks the SHAPE.

use SaddlePHP\Actions\Action;
use SaddlePHP\Fields\BelongsTo;
use SaddlePHP\Fields\FileUpload;
use SaddlePHP\Fields\Markdown;
use SaddlePHP\Fields\Number;
use SaddlePHP\Fields\Select;
use SaddlePHP\Fields\Text;
use SaddlePHP\Fields\Textarea;
use SaddlePHP\Fields\Toggle;
use SaddlePHP\Tables\Columns\BadgeColumn;
use SaddlePHP\Tables\Columns\BooleanColumn;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Filters\BooleanFilter;
use SaddlePHP\Tables\Filters\SelectFilter;

$base = ['component', 'name', 'label', 'required', 'placeholder', 'helper', 'value'];

it('locks the base field payload key set', function () use ($base) {
    expect(array_keys(Toggle::make('active')->toArray()))->toBe($base)
        ->and(array_keys(Markdown::make('notes')->toArray()))->toBe($base);
});

it('locks each field type\'s meta keys', function () use ($base) {
    expect(array_keys(Text::make('name')->toArray()))->toBe([...$base, 'type'])
        ->and(array_keys(Textarea::make('bio')->toArray()))->toBe([...$base, 'rows'])
        ->and(array_keys(Number::make('age')->toArray()))->toBe([...$base, 'type', 'min', 'max', 'step'])
        ->and(array_keys(Select::make('breed')->toArray()))->toBe([...$base, 'options'])
        ->and(array_keys(FileUpload::make('photo')->toArray()))->toBe([...$base, 'accept']);
});

it('appends the span key only when columnSpan is set', function () use ($base) {
    expect(array_keys(Text::make('name')->toArray()))->not->toContain('span')
        ->and(array_keys(Text::make('name')->columnSpan(2)->toArray()))->toBe([...$base, 'type', 'span']);
});

it('locks the belongsTo searchable vs non-searchable payload', function () use ($base) {
    expect(array_keys(BelongsTo::make('rider')->toArray()))->toBe([...$base, 'options'])
        ->and(array_keys(BelongsTo::make('rider')->searchable()->toArray()))->toBe([...$base, 'async', 'options']);
});

it('locks the column payloads', function () {
    expect(array_keys(TextColumn::make('name')->toArray()))->toBe(['name', 'label', 'sortable', 'type'])
        ->and(array_keys(BadgeColumn::make('breed')->toArray()))->toBe(['name', 'label', 'sortable', 'type', 'colors'])
        ->and(array_keys(BooleanColumn::make('active')->toArray()))->toBe(['name', 'label', 'sortable', 'type']);
});

it('locks the filter and action payloads', function () {
    expect(array_keys(SelectFilter::make('breed')->toArray()))->toBe(['name', 'label', 'type', 'options'])
        ->and(array_keys(BooleanFilter::make('active')->toArray()))->toBe(['name', 'label', 'type'])
        ->and(array_keys(Action::make('publish')->toArray()))->toBe(['name', 'label', 'color', 'confirm']);
});
