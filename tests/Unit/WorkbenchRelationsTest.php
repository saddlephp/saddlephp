<?php

declare(strict_types=1);

use Workbench\App\Models\Horse;
use Workbench\App\Models\Rider;

it('relates a horse to its rider', function () {
    $rider = Rider::factory()->create(['name' => 'Tex']);
    $horse = Horse::factory()->create(['rider_id' => $rider->id]);

    expect($horse->rider)->toBeInstanceOf(Rider::class)
        ->and($horse->rider->name)->toBe('Tex');
});

it('casts the new horse attributes', function () {
    $horse = Horse::factory()->create(['age' => 7, 'foaled_on' => '2019-05-01']);

    expect($horse->age)->toBe(7)
        ->and($horse->foaled_on)->toBeInstanceOf(DateTimeInterface::class);
});
