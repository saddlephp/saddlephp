<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use RodeoPHP\Rodeo;
use Workbench\App\Rodeo\HorseResource;

it('registers resources and resolves them by uri key', function () {
    $rodeo = new Rodeo;
    $rodeo->register([HorseResource::class]);

    expect($rodeo->resources()->all())->toBe([HorseResource::class])
        ->and($rodeo->resourceFor('horses'))->toBe(HorseResource::class)
        ->and($rodeo->resourceFor('unicorns'))->toBeNull();
});

it('builds grouped nav with active detection', function () {
    $rodeo = new Rodeo;
    $rodeo->register([HorseResource::class]);

    $request = Request::create('/admin/resources/horses');
    $nav = $rodeo->nav($request);

    expect($nav)->toHaveCount(1)
        ->and($nav[0]['group'])->toBeNull()
        ->and($nav[0]['items'][0])->toMatchArray([
            'label' => 'Horses', 'uriKey' => 'horses', 'active' => true,
        ]);
});

it('exposes the configured base path trimmed', function () {
    config(['rodeo.path' => '/ranch/']);

    expect((new Rodeo)->path())->toBe('ranch');
});
