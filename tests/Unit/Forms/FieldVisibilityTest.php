<?php

declare(strict_types=1);

use SaddlePHP\Fields\Text;
use SaddlePHP\Forms\Form;
use Workbench\App\Models\Horse;

it('shows every field by default', function () {
    $form = Form::make()->schema([Text::make('name'), Text::make('breed')]);

    expect($form->visibleFields())->toHaveCount(2)
        ->and(collect($form->toInertia())->pluck('name')->all())->toBe(['name', 'breed'])
        ->and($form->rules())->toHaveKeys(['name', 'breed']);
});

it('excludes hidden fields from the payload and rules', function () {
    $form = Form::make()->schema([
        Text::make('name'),
        Text::make('breed')->required()->canSee(fn () => false),
    ]);

    expect($form->visibleFields())->toHaveCount(1)
        ->and(collect($form->toInertia())->pluck('name')->all())->toBe(['name'])
        ->and($form->rules())->toHaveKey('name')
        ->and($form->rules())->not->toHaveKey('breed');
});

it('refuses to fill hidden fields', function () {
    $form = Form::make()->schema([
        Text::make('name'),
        Text::make('breed')->canSee(fn () => false),
    ]);

    $horse = new Horse;
    $form->fill($horse, ['name' => 'Cisco', 'breed' => 'quarter']);

    expect($horse->name)->toBe('Cisco')
        ->and($horse->breed)->toBeNull();
});

it('keeps fields() returning the full schema', function () {
    $form = Form::make()->schema([
        Text::make('name'),
        Text::make('breed')->canSee(fn () => false),
    ]);

    expect($form->fields())->toHaveCount(2);
});

it('passes the request to the callback', function () {
    $field = Text::make('name')->canSee(fn ($request) => $request->query('show') === 'yes');

    $requestYes = \Illuminate\Http\Request::create('/x', 'GET', ['show' => 'yes']);
    $requestNo  = \Illuminate\Http\Request::create('/x', 'GET', []);

    expect($field->visibleTo($requestYes))->toBeTrue()
        ->and($field->visibleTo($requestNo))->toBeFalse();
});
