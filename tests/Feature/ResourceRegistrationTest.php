<?php

declare(strict_types=1);

use SaddlePHP\Saddle;
use SaddlePHP\Tests\Fixtures\Discovery\PonyResource;
use Workbench\App\Saddle\HorseResource;

/**
 * Registration and discovery have to coexist.
 *
 * They used to be mutually exclusive: resources() early-returned the registered
 * list whenever it was non-empty, so discovery never ran. Since plugins
 * register from their own service providers, installing any plugin silently
 * emptied the host application's panel -- every application resource vanished
 * from the nav, from global search, and every /resources/{key} route 404'd.
 *
 * The docs promised the opposite the whole time: resources.md says "no manual
 * registration is needed" and plugins.md says register() "adds" to the list.
 */
beforeEach(function () {
    config([
        'saddle.resources.path' => __DIR__.'/../Fixtures/Discovery',
        'saddle.resources.namespace' => 'SaddlePHP\\Tests\\Fixtures\\Discovery',
    ]);
});

it('keeps discovered resources when a plugin registers its own', function () {
    $saddle = app(Saddle::class);

    $saddle->register([HorseResource::class]);

    expect($saddle->resources()->all())
        ->toContain(PonyResource::class)
        ->toContain(HorseResource::class);
});

it('does not duplicate a resource that is both discovered and registered', function () {
    $saddle = app(Saddle::class);

    $saddle->register([PonyResource::class]);

    expect(collect($saddle->resources())->filter(fn ($r) => $r === PonyResource::class))
        ->toHaveCount(1);
});

it('can still opt out of discovery entirely', function () {
    config(['saddle.resources.discovery' => false]);

    $saddle = app(Saddle::class);
    $saddle->register([HorseResource::class]);

    expect($saddle->resources()->all())
        ->toContain(HorseResource::class)
        ->not->toContain(PonyResource::class);
});

it('resolves a discovered resource by uri key alongside a registered one', function () {
    $saddle = app(Saddle::class);
    $saddle->register([HorseResource::class]);

    expect($saddle->resourceFor(PonyResource::uriKey()))->toBe(PonyResource::class)
        ->and($saddle->resourceFor(HorseResource::uriKey()))->toBe(HorseResource::class);
});
