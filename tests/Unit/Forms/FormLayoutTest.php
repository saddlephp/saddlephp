<?php

declare(strict_types=1);

use SaddlePHP\Fields\Number;
use SaddlePHP\Fields\Select;
use SaddlePHP\Fields\Text;
use SaddlePHP\Fields\Toggle;
use SaddlePHP\Forms\Form;
use SaddlePHP\Forms\Layout\Grid;
use SaddlePHP\Forms\Layout\Section;
use SaddlePHP\Forms\Layout\Tab;
use SaddlePHP\Forms\Layout\Tabs;
use Workbench\App\Models\Horse;

/*
 * v0.6 compat shape lock. A flat schema (no layout containers) MUST serialize
 * byte-identically to the v0.6 payload: a flat list of leaf field arrays with
 * no `layout` wrappers and no stray keys (e.g. no `span` on span-less fields).
 * This test is written against the untouched code first and must stay green
 * after the tree refactor.
 */
it('serializes a flat schema byte-identically to v0.6', function () {
    $form = Form::make()->schema([
        Text::make('name')->required(),
        Select::make('breed')->options(['arabian' => 'Arabian', 'quarter' => 'Quarter']),
        Toggle::make('is_saddled'),
    ]);

    expect($form->toInertia())->toBe([
        [
            'component' => 'text-field',
            'name' => 'name',
            'label' => 'Name',
            'required' => true,
            'placeholder' => null,
            'helper' => null,
            'value' => null,
            'type' => 'text',
        ],
        [
            'component' => 'select-field',
            'name' => 'breed',
            'label' => 'Breed',
            'required' => false,
            'placeholder' => null,
            'helper' => null,
            'value' => null,
            'options' => [
                ['value' => 'arabian', 'label' => 'Arabian'],
                ['value' => 'quarter', 'label' => 'Quarter'],
            ],
        ],
        [
            'component' => 'toggle-field',
            'name' => 'is_saddled',
            'label' => 'Is Saddled',
            'required' => false,
            'placeholder' => null,
            'helper' => null,
            'value' => false,
        ],
    ]);
});

/*
 * A span-less field must NOT carry a `span` key (flat-compat). Only a field
 * with columnSpan() set serializes `span`.
 */
it('omits the span key on a field without columnSpan', function () {
    $payload = Form::make()->schema([Text::make('name')])->toInertia();

    expect($payload[0])->not->toHaveKey('span');
});

it('serializes span only when columnSpan is set', function () {
    $payload = Form::make()->schema([
        Text::make('name')->columnSpan(2),
        Text::make('breed'),
    ])->toInertia();

    expect($payload[0]['span'])->toBe(2)
        ->and($payload[1])->not->toHaveKey('span');
});

/*
 * Flattening: fields() returns ALL leaves depth-first in declaration order,
 * regardless of nesting through Section > Grid and Tabs > Tab.
 */
function nestedForm(): Form
{
    return Form::make()->schema([
        Section::make('Identity')->description('Who this horse is.')->schema([
            Grid::make(2)->schema([
                Text::make('name')->required(),
                Text::make('breed'),
            ]),
            Select::make('color')->options(['bay' => 'Bay']),
        ]),
        Tabs::make([
            Tab::make('Care')->schema([
                Number::make('age'),
                Toggle::make('is_saddled'),
            ]),
            Tab::make('Notes')->schema([
                Text::make('notes'),
            ]),
        ]),
    ]);
}

it('flattens leaves depth-first in declaration order', function () {
    expect(collect(nestedForm()->fields())->map->name()->all())
        ->toBe(['name', 'breed', 'color', 'age', 'is_saddled', 'notes']);
});

it('aggregates rules across all nested leaves', function () {
    expect(array_keys(nestedForm()->rules()))
        ->toBe(['name', 'breed', 'color', 'age', 'is_saddled', 'notes']);
});

it('fills a leaf nested inside a tab', function () {
    $horse = new Horse;

    nestedForm()->fill($horse, ['age' => 7, 'name' => 'Cisco']);

    expect($horse->age)->toBe(7)
        ->and($horse->name)->toBe('Cisco');
});

/*
 * Tree serialization: container layout shapes with recursively-serialized
 * schema, leaf fields serialized exactly as v0.6.
 */
it('serializes a section as a layout node with a nested schema', function () {
    $payload = Form::make()->schema([
        Section::make('Identity')->description('Who this horse is.')->schema([
            Text::make('name'),
        ]),
    ])->toInertia();

    expect($payload)->toHaveCount(1)
        ->and($payload[0]['layout'])->toBe('section')
        ->and($payload[0]['label'])->toBe('Identity')
        ->and($payload[0]['description'])->toBe('Who this horse is.')
        ->and($payload[0]['schema'])->toHaveCount(1)
        ->and($payload[0]['schema'][0]['name'])->toBe('name')
        ->and($payload[0]['schema'][0]['component'])->toBe('text-field');
});

it('serializes a grid with its column count', function () {
    $payload = Form::make()->schema([
        Grid::make(3)->schema([
            Text::make('name'),
            Text::make('breed'),
        ]),
    ])->toInertia();

    expect($payload[0]['layout'])->toBe('grid')
        ->and($payload[0]['columns'])->toBe(3)
        ->and(collect($payload[0]['schema'])->pluck('name')->all())->toBe(['name', 'breed']);
});

it('serializes tabs as a list of tab nodes each with a schema', function () {
    $payload = Form::make()->schema([
        Tabs::make([
            Tab::make('Care')->schema([Number::make('age')]),
            Tab::make('Notes')->schema([Text::make('notes')]),
        ]),
    ])->toInertia();

    expect($payload[0]['layout'])->toBe('tabs')
        ->and($payload[0]['tabs'])->toHaveCount(2)
        ->and($payload[0]['tabs'][0]['label'])->toBe('Care')
        ->and($payload[0]['tabs'][0]['schema'][0]['name'])->toBe('age')
        ->and($payload[0]['tabs'][1]['label'])->toBe('Notes')
        ->and($payload[0]['tabs'][1]['schema'][0]['name'])->toBe('notes');
});

/*
 * canSee-hidden leaf is excluded from the tree, while its siblings and the
 * containing layout node survive.
 */
it('excludes a hidden leaf from the tree but keeps its container and siblings', function () {
    $payload = Form::make()->schema([
        Section::make('Identity')->schema([
            Text::make('name'),
            Text::make('secret')->canSee(fn () => false),
        ]),
    ])->toInertia();

    expect($payload)->toHaveCount(1)
        ->and($payload[0]['layout'])->toBe('section')
        ->and(collect($payload[0]['schema'])->pluck('name')->all())->toBe(['name']);
});

it('drops hidden leaves from rules and fill through containers', function () {
    $form = Form::make()->schema([
        Section::make('Identity')->schema([
            Text::make('name'),
            Text::make('secret')->canSee(fn () => false),
        ]),
    ]);

    $horse = new Horse;
    $form->fill($horse, ['name' => 'Cisco', 'secret' => 'shh']);

    expect($form->rules())->toHaveKey('name')
        ->and($form->rules())->not->toHaveKey('secret')
        ->and($horse->name)->toBe('Cisco')
        ->and($horse->secret)->toBeNull();
});

it('serializes columnSpan on a leaf inside a grid', function () {
    $payload = Form::make()->schema([
        Grid::make(2)->schema([
            Text::make('name')->columnSpan(2),
            Text::make('breed'),
        ]),
    ])->toInertia();

    expect($payload[0]['schema'][0]['span'])->toBe(2)
        ->and($payload[0]['schema'][1])->not->toHaveKey('span');
});
