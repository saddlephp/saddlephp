<?php

declare(strict_types=1);

use SaddlePHP\Saddle;

it('has no greeting until one is configured', function () {
    config(['saddle.brand.greeting' => null, 'saddle.brand.subgreeting' => null]);

    expect((new Saddle)->greeting())->toBeNull()
        ->and((new Saddle)->greeting('Matthew'))->toBeNull()
        ->and((new Saddle)->subgreeting())->toBeNull();
});

it('treats a blank or non-string greeting as unconfigured', function (mixed $configured) {
    config(['saddle.brand.greeting' => $configured, 'saddle.brand.subgreeting' => $configured]);

    expect((new Saddle)->greeting('Matthew'))->toBeNull()
        ->and((new Saddle)->subgreeting())->toBeNull();
})->with([
    'empty string' => '',
    'array' => [['Welcome']],
    'integer' => 7,
    'boolean' => true,
]);

it('returns the configured subgreeting verbatim', function () {
    config(['saddle.brand.subgreeting' => 'Spend, by channel, for the last 30 days.']);

    expect((new Saddle)->subgreeting())->toBe('Spend, by channel, for the last 30 days.');
});

it('interpolates the signed-in name', function (string $configured, string $named) {
    config(['saddle.brand.greeting' => $configured]);

    expect((new Saddle)->greeting('Matthew'))->toBe($named);
})->with([
    ['Welcome, :name.', 'Welcome, Matthew.'],
    ['Howdy :name', 'Howdy Matthew'],
    [':name — spend', 'Matthew — spend'],
    ['Marketing', 'Marketing'],
]);

/**
 * The reason this is a preg_replace and not a str_replace(':name', '').
 *
 * A plain removal leaves the placeholder's punctuation stranded, so the very
 * first screen a panel shows with nobody signed in reads "Welcome, ." -- which
 * is worse than the default wording it replaced. The separator in front of
 * `:name` goes with it.
 *
 * A separator *after* a leading `:name` is deliberately kept: dropping it would
 * mangle "Howdy :name," (the commoner shape) into "Howdy". ":name — spend"
 * therefore degrades to "— spend", which is a known rough edge, not an
 * oversight -- put the placeholder last if that matters to you.
 */
it('removes the placeholder and its leading separator when nobody is signed in', function (string $configured, string $anonymous) {
    config(['saddle.brand.greeting' => $configured]);

    expect((new Saddle)->greeting())->toBe($anonymous)
        ->and((new Saddle)->greeting(''))->toBe($anonymous);
})->with([
    ['Welcome, :name.', 'Welcome.'],
    ['Howdy :name', 'Howdy'],
    [':name — spend', '— spend'],
    ['Marketing', 'Marketing'],
    ['Howdy :name, welcome back', 'Howdy, welcome back'],
    ['Welcome; :name!', 'Welcome!'],
    ['Report for: :name', 'Report for'],
]);

it('collapses the gap a removed placeholder leaves mid-sentence', function () {
    // Defensive: the separator and the whitespace *before* the placeholder go
    // with it, so only whitespace that followed it can double up.
    config(['saddle.brand.greeting' => 'Good morning :name  — here is today.']);

    expect((new Saddle)->greeting())->toBe('Good morning — here is today.');
});
