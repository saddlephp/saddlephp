<?php

declare(strict_types=1);

use RodeoPHP\Rodeo;
use Workbench\App\Rodeo\HorseResource;

it('reports its version', function () {
    expect((new Rodeo)->version())->toBe(Rodeo::VERSION)
        ->and(Rodeo::VERSION)->toMatch('/^\d+\.\d+\.\d+/');
});

it('greets like a cowboy', function () {
    expect((new Rodeo)->greeting())->toContain('admin panel in town');
});

it('deduplicates registered resources', function () {
    $rodeo = new Rodeo;
    $rodeo->register([HorseResource::class]);
    $rodeo->register([HorseResource::class]);

    expect($rodeo->resources())->toHaveCount(1);
});
