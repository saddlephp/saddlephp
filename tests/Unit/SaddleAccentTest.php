<?php

declare(strict_types=1);

use SaddlePHP\Saddle;

it('passes through a valid hex accent', function () {
    config(['saddle.brand.accent' => '#1a2b3c']);

    expect((new Saddle)->accent())->toBe('#1a2b3c');
});

it('passes through short and 8-digit hex accents', function () {
    config(['saddle.brand.accent' => '#abc']);
    expect((new Saddle)->accent())->toBe('#abc');

    config(['saddle.brand.accent' => '#11223344']);
    expect((new Saddle)->accent())->toBe('#11223344');
});

it('passes through rgb, hsl and oklch function accents', function () {
    config(['saddle.brand.accent' => 'rgb(217, 80, 31)']);
    expect((new Saddle)->accent())->toBe('rgb(217, 80, 31)');

    config(['saddle.brand.accent' => 'hsl(20, 75%, 49%)']);
    expect((new Saddle)->accent())->toBe('hsl(20, 75%, 49%)');

    config(['saddle.brand.accent' => 'oklch(0.7 0.15 40)']);
    expect((new Saddle)->accent())->toBe('oklch(0.7 0.15 40)');
});

it('falls back to the default when the accent tries to break out of the style block', function () {
    config(['saddle.brand.accent' => 'red} body{display:none']);

    expect((new Saddle)->accent())->toBe('#d9501f');
});

it('falls back to the default for a missing accent', function () {
    config(['saddle.brand.accent' => null]);

    expect((new Saddle)->accent())->toBe('#d9501f');
});
