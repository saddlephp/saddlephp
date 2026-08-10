<?php

declare(strict_types=1);

use SaddlePHP\Actions\Action;
use SaddlePHP\Actions\BulkAction;
use Workbench\App\Models\Horse;

// ---------------------------------------------------------------------------
// Action defaults
// ---------------------------------------------------------------------------

it('derives a headline label from the name by default', function () {
    $action = Action::make('unsaddle-horse');

    expect($action->name())->toBe('unsaddle-horse')
        ->and($action->toArray()['label'])->toBe('Unsaddle Horse');
});

it('defaults color to ink', function () {
    expect(Action::make('foo')->toArray()['color'])->toBe('ink');
});

it('defaults confirm to null', function () {
    expect(Action::make('foo')->toArray()['confirm'])->toBeNull();
});

it('defaults success message to Done.', function () {
    expect(Action::make('foo')->message())->toBe('Done.');
});

/**
 * Actions run arbitrary mutating code, so an undeclared ability has to mean
 * "check update", not "anyone who can see the index". The old null default let
 * a read-only account invoke every action a developer had not annotated.
 */
it('defaults ability to update so an unannotated action is not fail-open', function () {
    expect(Action::make('foo')->ability())->toBe('update');
});

it('withoutAuthorization() is the explicit opt-out', function () {
    expect(Action::make('foo')->withoutAuthorization()->ability())->toBeNull();
});

it('defaults callback to null', function () {
    expect(Action::make('foo')->callback())->toBeNull();
});

// ---------------------------------------------------------------------------
// Action fluents
// ---------------------------------------------------------------------------

it('accepts a custom label', function () {
    $action = Action::make('foo')->label('My Label');

    expect($action->toArray()['label'])->toBe('My Label');
});

it('accepts a color string', function () {
    $action = Action::make('foo')->color('accent');

    expect($action->toArray()['color'])->toBe('accent');
});

it('accepts any color string token', function () {
    $action = Action::make('foo')->color('muted');

    expect($action->toArray()['color'])->toBe('muted');
});

it('stores Are you sure when requiresConfirmation is called with no argument', function () {
    $action = Action::make('foo')->requiresConfirmation();

    expect($action->toArray()['confirm'])->toBe('Are you sure?');
});

it('stores a custom confirmation message', function () {
    $action = Action::make('foo')->requiresConfirmation('Really do this?');

    expect($action->toArray()['confirm'])->toBe('Really do this?');
});

it('stores an authorize ability', function () {
    $action = Action::make('foo')->authorize('update');

    expect($action->ability())->toBe('update');
});

it('stores a handle callback', function () {
    $cb = fn ($record) => null;
    $action = Action::make('foo')->handle($cb);

    expect($action->callback())->toBe($cb);
});

it('stores a success message', function () {
    $action = Action::make('foo')->successMessage('Horse unsaddled.');

    expect($action->message())->toBe('Horse unsaddled.');
});

// ---------------------------------------------------------------------------
// Action::toArray exact shape
// ---------------------------------------------------------------------------

it('toArray returns the exact expected shape', function () {
    $action = Action::make('do-thing')
        ->label('Do Thing')
        ->color('accent')
        ->requiresConfirmation('Are you certain?');

    expect($action->toArray())->toBe([
        'name' => 'do-thing',
        'label' => 'Do Thing',
        'color' => 'accent',
        'confirm' => 'Are you certain?',
    ]);
});

it('toArray shape has name label color confirm keys only', function () {
    $keys = array_keys(Action::make('x')->toArray());

    expect($keys)->toBe(['name', 'label', 'color', 'confirm']);
});

// ---------------------------------------------------------------------------
// BulkAction::delete() preset
// ---------------------------------------------------------------------------

it('BulkAction::delete preset has name delete', function () {
    expect(BulkAction::delete()->name())->toBe('delete');
});

it('BulkAction::delete preset has label Delete', function () {
    expect(BulkAction::delete()->toArray()['label'])->toBe('Delete');
});

it('BulkAction::delete preset has color accent', function () {
    expect(BulkAction::delete()->toArray()['color'])->toBe('accent');
});

it('BulkAction::delete preset stores the confirmation message', function () {
    expect(BulkAction::delete()->toArray()['confirm'])->toBe('Delete the selected records?');
});

it('BulkAction::delete preset has authorize delete ability', function () {
    expect(BulkAction::delete()->ability())->toBe('delete');
});

it('BulkAction::delete preset handle deletes records', function () {
    $a = Horse::factory()->create();
    $b = Horse::factory()->create();

    $collection = collect([$a, $b]);
    $cb = BulkAction::delete()->callback();

    expect($cb)->toBeInstanceOf(Closure::class);

    $cb($collection);

    expect(Horse::find($a->id))->toBeNull()
        ->and(Horse::find($b->id))->toBeNull();
});

it('BulkAction extends Action', function () {
    expect(BulkAction::delete())->toBeInstanceOf(Action::class);
});
