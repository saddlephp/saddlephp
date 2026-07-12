<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use SaddlePHP\Saddle;

it('merges shared props from registered sharing callbacks into the payload', function () {
    app(Saddle::class)->sharing(fn () => ['alpha' => 1]);
    app(Saddle::class)->sharing(fn (Request $request) => ['beta' => 2]);
    $this->actingAsUser();

    $this->get('/admin/resources/horses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('saddle.alpha', 1)
            ->where('saddle.beta', 2)
        );
});

it('ignores a sharing callback that throws instead of breaking the page', function () {
    app(Saddle::class)->sharing(fn () => throw new RuntimeException('boom'));
    app(Saddle::class)->sharing(fn () => ['ok' => 1]);
    $this->actingAsUser();

    $this->get('/admin/resources/horses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('saddle.ok', 1));
});

it('ignores a sharing callback that returns a non-array', function () {
    app(Saddle::class)->sharing(fn () => 'not-an-array');
    $this->actingAsUser();

    $this->get('/admin/resources/horses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->missing('saddle.0'));
});

it('falls back to the untransformed nav when navUsing throws', function () {
    app(Saddle::class)->navUsing(fn () => throw new RuntimeException('boom'));

    // The resource-derived nav still renders instead of 500-ing every page.
    expect(app(Saddle::class)->nav(new Request))->not->toBeEmpty();
});

it('never lets a sharing callback clobber a core saddle key', function () {
    app(Saddle::class)->sharing(fn () => ['path' => 'hijacked']);
    $this->actingAsUser();

    $this->get('/admin/resources/horses')
        ->assertInertia(fn (Assert $page) => $page->where('saddle.path', 'admin'));
});

it('applies the navUsing transform to the navigation', function () {
    app(Saddle::class)->navUsing(fn (array $nav) => [...$nav, ['group' => 'External', 'items' => []]]);

    $nav = app(Saddle::class)->nav(new Request);

    expect(end($nav)['group'])->toBe('External');
});

it('rejects a theme token name with a trailing newline', function () {
    config()->set('saddle.brand.theme', ["bad\n" => '#123456']);
    // Token names are not trimmed, so the strict end-anchor (D flag) is what
    // keeps a newline-suffixed token from being registered and injected.
    app(Saddle::class)->registerThemeTokens("bad\n");

    expect(app(Saddle::class)->theme())->toBe([]);
});

it('extends the theme allowlist with registered tokens and rejects unsafe names', function () {
    config()->set('saddle.brand.theme', [
        'brand-x' => '#123456',   // registered below -> kept
        'ink' => '#222222',       // built-in -> kept
        'skipme' => '#000000',    // not allowed -> dropped
    ]);
    app(Saddle::class)->registerThemeTokens('brand-x');
    app(Saddle::class)->registerThemeTokens('bad token'); // invalid name -> ignored

    expect(app(Saddle::class)->theme())->toBe(['brand-x' => '#123456', 'ink' => '#222222']);
});
