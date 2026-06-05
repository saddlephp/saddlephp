<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use SaddlePHP\Saddle;
use SaddlePHP\Tests\Fixtures\BrokenResource;
use Workbench\App\Saddle\HorseResource;

it('registers resources and resolves them by uri key', function () {
    $saddle = new Saddle;
    $saddle->register([HorseResource::class]);

    expect($saddle->resources()->all())->toBe([HorseResource::class])
        ->and($saddle->resourceFor('horses'))->toBe(HorseResource::class)
        ->and($saddle->resourceFor('unicorns'))->toBeNull();
});

it('builds grouped nav with active detection', function () {
    $saddle = new Saddle;
    $saddle->register([HorseResource::class]);

    $request = Request::create('/admin/resources/horses');
    $nav = $saddle->nav($request);

    expect($nav)->toHaveCount(1)
        ->and($nav[0]['group'])->toBeNull()
        ->and($nav[0]['items'][0])->toMatchArray([
            'label' => 'Horses', 'uriKey' => 'horses', 'active' => true,
        ]);
});

it('keeps the nav standing when one resource breaks', function () {
    $saddle = new Saddle;
    $saddle->register([HorseResource::class, BrokenResource::class]);

    $nav = $saddle->nav(Request::create('/admin'));

    $labels = collect($nav)->flatMap(fn (array $group) => $group['items'])
        ->pluck('label')->all();

    expect($labels)->toContain('Horses')
        ->and($labels)->not->toContain('Brokens');
});

it('exposes the configured base path trimmed', function () {
    config(['saddle.path' => '/ranch/']);

    expect((new Saddle)->path())->toBe('ranch');
});
