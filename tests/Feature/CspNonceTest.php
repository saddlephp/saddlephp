<?php

declare(strict_types=1);

use SaddlePHP\Saddle;
use Workbench\App\Models\Horse;

/**
 * The panel shell carries one inline <script>: the pre-paint dark-mode
 * bootstrap. Under a strict Content-Security-Policy the browser refuses it,
 * which costs a violation on every panel page and a flash of the light theme
 * on every navigation for dark-mode users.
 *
 * The nonce is host-supplied through a callback, and for the panel that
 * supplies none -- the overwhelmingly common case -- the shell must render
 * byte-identically to 1.3.0. That is what the first two tests pin, including
 * the whitespace: the `@php` sits on the same line as the tag precisely so it
 * contributes nothing to the output.
 */
it('renders the theme script exactly as before when no nonce resolver is registered', function () {
    $this->actingAsUser();

    $content = $this->get('/admin')->assertOk()->getContent();

    expect($content)->toContain("<script>\n        (function () {")
        ->and($content)->not->toContain('nonce=');
});

it('renders byte-identical markup when the resolver yields no nonce', function () {
    $this->actingAsUser();

    $before = $this->get('/admin')->assertOk()->getContent();

    app(Saddle::class)->resolveNonceUsing(fn () => null);

    expect($this->get('/admin')->assertOk()->getContent())->toBe($before);
});

it('stamps the resolved nonce onto the theme script', function () {
    app(Saddle::class)->resolveNonceUsing(fn () => 'r0dE0nOnc3');
    $this->actingAsUser();

    $this->get('/admin')
        ->assertOk()
        ->assertSee('<script nonce="r0dE0nOnc3">', false);
});

it('resolves a fresh nonce for every panel render', function () {
    $calls = 0;
    app(Saddle::class)->resolveNonceUsing(function () use (&$calls) {
        $calls++;

        return 'n'.$calls;
    });
    $this->actingAsUser();

    $this->get('/admin')->assertOk()->assertSee('<script nonce="n1">', false);
    $this->get('/admin')->assertOk()->assertSee('<script nonce="n2">', false);
});

it('escapes the nonce so it cannot break out of the attribute', function () {
    app(Saddle::class)->resolveNonceUsing(fn () => 'abc" onload="alert(1)');
    $this->actingAsUser();

    $this->get('/admin')
        ->assertOk()
        ->assertSee('<script nonce="abc&quot; onload=&quot;alert(1)">', false);
});

it('still renders the panel when the host nonce provider throws', function () {
    app(Saddle::class)->resolveNonceUsing(fn () => throw new RuntimeException('csp.nonce is not bound'));
    $this->actingAsUser();
    Horse::factory()->create();

    expect($this->get('/admin/resources/horses')->assertOk()->getContent())
        ->toContain("<script>\n        (function () {");
});
