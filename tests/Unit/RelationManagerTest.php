<?php

declare(strict_types=1);

use SaddlePHP\Forms\Form;
use SaddlePHP\RelationManager;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Ranch;
use Workbench\App\Saddle\RelationManagers\HorsesRelationManager;

it('derives the related model and uri key from the relationship', function () {
    $ranch = new Ranch;

    expect(HorsesRelationManager::relatedModel($ranch))->toBe(Horse::class)
        ->and(HorsesRelationManager::uriKey())->toBe('horses')
        ->and(HorsesRelationManager::label())->toBe('Horses')
        ->and(HorsesRelationManager::singularLabel())->toBe('Horse');
});

it('scopes its query to the parent', function () {
    $ranchA = Ranch::factory()->create();
    $ranchB = Ranch::factory()->create();
    $ranchA->horses()->create(['name' => 'Cisco']);
    $ranchB->horses()->create(['name' => 'Dakota']);

    $names = HorsesRelationManager::relationFor($ranchA)->pluck('name')->all();

    expect($names)->toBe(['Cisco']);
});

it('makes a related instance with the parent key already set', function () {
    $ranch = Ranch::factory()->create();

    $horse = HorsesRelationManager::newRelatedFor($ranch);

    expect($horse)->toBeInstanceOf(Horse::class)
        ->and($horse->ranch_id)->toBe($ranch->id);
});

it('throws when the relationship is not a HasMany', function () {
    expect(fn () => BadRelationManager::relatedModel(new Ranch))->toThrow(LogicException::class);
});

it('throws when the relationship method is missing', function () {
    expect(fn () => MissingRelationManager::relatedModel(new Ranch))->toThrow(LogicException::class);
});

class BadRelationManager extends RelationManager
{
    protected static string $relationship = 'users'; // Ranch::users() is BelongsToMany

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function form(Form $form): Form
    {
        return $form;
    }
}

class MissingRelationManager extends RelationManager
{
    protected static string $relationship = 'unicorns'; // no such method

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function form(Form $form): Form
    {
        return $form;
    }
}
