<?php

declare(strict_types=1);

use Workbench\App\Models\Horse;
use Workbench\App\Rodeo\HorseResource;

it('derives labels and uri key from the class name', function () {
    expect(HorseResource::label())->toBe('Horses')
        ->and(HorseResource::singularLabel())->toBe('Horse')
        ->and(HorseResource::uriKey())->toBe('horses');
});

it('creates new model instances and base queries', function () {
    expect(HorseResource::newModel())->toBeInstanceOf(Horse::class)
        ->and(HorseResource::query(request())->getModel())->toBeInstanceOf(Horse::class);
});

it('titles records via the configured title attribute', function () {
    $horse = Horse::factory()->create(['name' => 'Cisco']);

    expect(HorseResource::recordTitle($horse))->toBe('Cisco');
});

it('allows all abilities when the model has no policy', function () {
    expect(HorseResource::allows('viewAny'))->toBeTrue()
        ->and(HorseResource::allows('create'))->toBeTrue();
});
