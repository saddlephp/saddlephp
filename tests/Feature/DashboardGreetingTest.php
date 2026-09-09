<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

/**
 * Dashboard.vue used to hard-code "Howdy{name}." and "Pick a resource and get
 * ridin'.", while Saddle::greeting() returned a different fixed string that
 * nothing rendered. So the one line every panel user reads first could not be
 * changed from the application at all, short of a JavaScript shim that
 * string-matched the default text in the DOM.
 *
 * Both lines are now shared props. null means "unconfigured", and the Vue
 * falls back to the existing wording -- so an untouched panel is unchanged.
 */
it('shares no greeting for an unconfigured panel', function () {
    $this->actingAsUser();

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('saddle.greeting', null)
            ->where('saddle.subgreeting', null)
        );
});

it('shares the configured greeting with the signed-in name interpolated', function () {
    config([
        'saddle.brand.greeting' => 'Welcome, :name.',
        'saddle.brand.subgreeting' => 'Spend, by channel, for the last 30 days.',
    ]);
    $this->actingAsUser(['name' => 'Matthew']);

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('saddle.greeting', 'Welcome, Matthew.')
            ->where('saddle.subgreeting', 'Spend, by channel, for the last 30 days.')
        );
});

/**
 * The fallback lives in the Vue, not the payload, so a panel that configures
 * nothing renders exactly what 1.3.0 rendered. There is no JS test runner here,
 * so pin it at the source -- the same way VersionParityTest pins vite.config.js.
 */
it('keeps the original wording as the frontend fallback', function () {
    $dashboard = (string) file_get_contents(__DIR__.'/../../resources/js/Pages/Dashboard.vue');

    expect($dashboard)->toContain('saddle.greeting ||')
        ->and($dashboard)->toContain('Howdy${saddle.user ? `, ${saddle.user.name}` : \'\'}.')
        ->and($dashboard)->toContain('saddle.subgreeting ||')
        ->and($dashboard)->toContain("Pick a resource and get ridin'.");
});

it('keeps the fallback wording out of the payload so the frontend owns it', function () {
    $this->actingAsUser(['name' => 'Matthew']);

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('saddle.greeting', null)
            ->where('saddle.user.name', 'Matthew')
        );
});
