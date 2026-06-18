<?php

declare(strict_types=1);

use SaddlePHP\Fields\Date;
use SaddlePHP\Fields\Markdown;
use SaddlePHP\Fields\Select;
use SaddlePHP\Fields\Toggle;
use Workbench\App\Models\Horse;

it('shows the option label for a select', function () {
    $horse = new Horse(['breed' => 'mustang']);
    $node = Select::make('breed')->options(['mustang' => 'Mustang', 'quarter' => 'Quarter Horse'])->toDisplay($horse);

    expect($node['type'])->toBe('text')->and($node['display'])->toBe('Mustang');
});

it('falls back to the raw value for an unknown select option', function () {
    $horse = new Horse(['breed' => 'palomino']);
    $node = Select::make('breed')->options(['mustang' => 'Mustang'])->toDisplay($horse);

    expect($node['display'])->toBe('palomino');
});

it('shows a boolean type for a toggle', function () {
    $horse = new Horse(['is_saddled' => true]);
    $node = Toggle::make('is_saddled')->toDisplay($horse);

    expect($node['type'])->toBe('boolean')->and($node['display'])->toBeTrue();
});

it('formats a date', function () {
    $horse = new Horse(['foaled_on' => '2021-05-01']);
    $node = Date::make('foaled_on')->toDisplay($horse);

    expect($node['display'])->toContain('2021')->and($node['display'])->toContain('May');
});

it('marks markdown for rich rendering', function () {
    $horse = new Horse(['notes' => '# Hello']);
    $node = Markdown::make('notes')->toDisplay($horse);

    expect($node['type'])->toBe('markdown')->and($node['display'])->toBe('# Hello');
});

it('shows a dash placeholder as null when the value is absent', function () {
    $node = Select::make('breed')->options(['mustang' => 'Mustang'])->toDisplay(new Horse);

    expect($node['display'])->toBeNull();
});
