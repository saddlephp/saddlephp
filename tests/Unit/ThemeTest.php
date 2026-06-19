<?php

declare(strict_types=1);

use SaddlePHP\Saddle;

it('returns only allowlisted, valid theme tokens', function () {
    config()->set('saddle.brand.theme', [
        'ink' => '#111111',
        'accent' => 'oklch(0.6 0.2 30)',
        'bogus' => '#fff',          // not an allowlisted token
        'bg' => 'red; } body {',     // malformed colour
    ]);

    expect((new Saddle)->theme())->toBe([
        'ink' => '#111111',
        'accent' => 'oklch(0.6 0.2 30)',
    ]);
});

it('is empty by default', function () {
    expect((new Saddle)->theme())->toBe([]);
});
