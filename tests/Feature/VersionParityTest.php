<?php

declare(strict_types=1);

use SaddlePHP\Saddle;

/**
 * The panel compares the server's version against the one compiled into the
 * bundle and shows a "run saddle:upgrade" banner when they differ.
 *
 * In 1.1.0 and 1.2.0 only src/Saddle.php was bumped, so every install compared
 * 1.2.0 against a bundle still stamped 1.0.1 and displayed that banner forever
 * -- and saddle:upgrade republished the same bundle, so following the
 * instruction could never clear it. The one drift signal the framework has was
 * crying wolf on every page of every install.
 *
 * vite.config.js now reads the constant directly, so the bundle cannot drift.
 * package.json carries the same number for npm consumers, and this test is what
 * keeps it honest.
 */
it('keeps package.json in lockstep with the PHP version constant', function () {
    $package = json_decode((string) file_get_contents(__DIR__.'/../../package.json'), true);

    expect($package)->toBeArray()
        ->and($package['version'])->toBe(Saddle::VERSION);
});

it('builds the bundle from the PHP constant rather than package.json', function () {
    $config = (string) file_get_contents(__DIR__.'/../../vite.config.js');

    // If someone re-points this at package.json, the two can drift again.
    expect($config)->toContain('src/Saddle.php')
        ->and($config)->toContain('__SADDLE_VERSION__');
});
