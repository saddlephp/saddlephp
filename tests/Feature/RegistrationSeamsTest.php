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
