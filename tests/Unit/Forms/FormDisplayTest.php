<?php

declare(strict_types=1);

use SaddlePHP\Fields\Text;
use SaddlePHP\Forms\Form;
use SaddlePHP\Forms\Layout\Section;
use Workbench\App\Models\Horse;

it('serializes a flat form to display nodes with formatted values', function () {
    $horse = new Horse(['name' => 'Cisco']);

    $nodes = Form::make()->model(new Horse)
        ->schema([Text::make('name')])
        ->toDisplay($horse);

    expect($nodes)->toHaveCount(1)
        ->and($nodes[0]['name'])->toBe('name')
        ->and($nodes[0]['label'])->toBe('Name')
        ->and($nodes[0]['type'])->toBe('text')
        ->and($nodes[0]['display'])->toBe('Cisco')
        ->and($nodes[0]['component'])->toBe('display-entry'); // walkable leaf marker, not an input
});

it('preserves the layout tree in display mode', function () {
    $horse = new Horse(['name' => 'Cisco']);

    $nodes = Form::make()->model(new Horse)
        ->schema([Section::make('Identity')->schema([Text::make('name')])])
        ->toDisplay($horse);

    expect($nodes[0]['layout'])->toBe('section')
        ->and($nodes[0]['schema'][0]['name'])->toBe('name')
        ->and($nodes[0]['schema'][0]['display'])->toBe('Cisco');
});

it('finds a display leaf with the shared findField helper', function () {
    $horse = new Horse(['name' => 'Cisco']);

    $nodes = Form::make()->model(new Horse)
        ->schema([Section::make('Identity')->schema([Text::make('name')])])
        ->toDisplay($horse);

    expect(findField($nodes, 'name')['display'])->toBe('Cisco');
});
