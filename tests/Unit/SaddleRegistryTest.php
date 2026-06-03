<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use SaddlePHP\Saddle;
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

it('exposes the configured base path trimmed', function () {
    config(['saddle.path' => '/ranch/']);

    expect((new Saddle)->path())->toBe('ranch');
});
