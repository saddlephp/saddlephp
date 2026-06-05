<?php

declare(strict_types=1);

use SaddlePHP\Saddle;

it('registers plugin scripts and styles in order without duplicates', function () {
    $saddle = new Saddle;

    $saddle->script('/vendor/a/one.js')
        ->script('/vendor/b/two.js')
        ->script('/vendor/a/one.js')
        ->style('/vendor/a/one.css')
        ->style('/vendor/a/one.css');

    expect($saddle->scripts())->toBe(['/vendor/a/one.js', '/vendor/b/two.js'])
        ->and($saddle->styles())->toBe(['/vendor/a/one.css']);
});

it('starts with no plugin assets', function () {
    expect((new Saddle)->scripts())->toBe([])
        ->and((new Saddle)->styles())->toBe([]);
});
