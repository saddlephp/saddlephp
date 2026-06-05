<?php

declare(strict_types=1);

use SaddlePHP\Saddle;
use Workbench\App\Models\Ranch;
use Workbench\App\Saddle\HorseResource;

it('reports its version', function () {
    expect((new Saddle)->version())->toBe(Saddle::VERSION)
        ->and(Saddle::VERSION)->toMatch('/^\d+\.\d+\.\d+/');
});

it('greets like a cowboy', function () {
    expect((new Saddle)->greeting())->toContain('admin panel in town');
});

it('deduplicates registered resources', function () {
    $saddle = new Saddle;
    $saddle->register([HorseResource::class]);
    $saddle->register([HorseResource::class]);

    expect($saddle->resources())->toHaveCount(1);
});

it('forgets the bound tenant so it cannot leak across long-lived requests', function () {
    $ranch = Ranch::factory()->create(['name' => 'Dusty Creek Ranch']);
    $saddle = new Saddle;

    $saddle->useTenant($ranch);
    expect($saddle->tenant())->not->toBeNull();

    $saddle->forgetTenant();
    expect($saddle->tenant())->toBeNull();
});
