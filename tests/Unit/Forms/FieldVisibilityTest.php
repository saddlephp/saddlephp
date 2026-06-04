<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use SaddlePHP\Fields\BelongsTo;
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

    $requestYes = Request::create('/x', 'GET', ['show' => 'yes']);
    $requestNo = Request::create('/x', 'GET', []);

    expect($field->visibleTo($requestYes))->toBeTrue()
        ->and($field->visibleTo($requestNo))->toBeFalse();
});

it('filters through the container request', function () {
    $this->app->instance('request', Request::create('/x', 'GET', ['show' => 'yes']));

    $form = Form::make()->schema([
        Text::make('name'),
        Text::make('breed')->canSee(fn ($request) => $request->query('show') === 'yes'),
        Text::make('notes')->canSee(fn ($request) => $request->query('show') === 'no'),
    ]);

    expect(collect($form->visibleFields())->map->name()->all())->toBe(['name', 'breed'])
        ->and($form->rules())->toHaveKeys(['name', 'breed'])
        ->and($form->rules())->not->toHaveKey('notes');
});

it('still binds hidden relation fields before filtering', function () {
    $field = BelongsTo::make('rider')->canSee(fn () => false);

    $form = Form::make()->model(new Horse)->schema([Text::make('name'), $field]);

    expect($form->visibleFields())->toHaveCount(1)
        ->and($field->name())->toBe('rider_id');
});

it('coerces truthy and falsy callback results', function () {
    $request = Request::create('/x');

    expect(Text::make('a')->canSee(fn () => 1)->visibleTo($request))->toBeTrue()
        ->and(Text::make('b')->canSee(fn () => null)->visibleTo($request))->toBeFalse();
});
