<?php

declare(strict_types=1);

use SaddlePHP\Saddle;

it('has no nonce until a resolver is registered', function () {
    expect((new Saddle)->nonce())->toBeNull();
});

it('returns the resolved nonce', function () {
    expect((new Saddle)->resolveNonceUsing(fn () => 'r0dE0nOnc3')->nonce())->toBe('r0dE0nOnc3');
});

it('calls the resolver on every read, because a reused nonce is not a nonce', function () {
    $calls = 0;
    $saddle = (new Saddle)->resolveNonceUsing(function () use (&$calls) {
        $calls++;

        return 'n'.$calls;
    });

    expect($saddle->nonce())->toBe('n1')
        ->and($saddle->nonce())->toBe('n2')
        ->and($calls)->toBe(2);
});

/**
 * A panel must not 500 because the host's nonce provider is misconfigured.
 * Without a nonce the browser refuses one inline script and the page still
 * works, minus the pre-paint theme class -- so every bad shape degrades to
 * "no nonce" rather than to an exception.
 */
it('degrades to no nonce for a resolver that misbehaves', function () {
    $saddle = new Saddle;

    $saddle->resolveNonceUsing(fn () => throw new RuntimeException('csp.nonce is not bound'));
    expect($saddle->nonce())->toBeNull();

    $saddle->resolveNonceUsing(fn () => '');
    expect($saddle->nonce())->toBeNull();

    $saddle->resolveNonceUsing(fn () => null);
    expect($saddle->nonce())->toBeNull();

    $saddle->resolveNonceUsing(fn () => 1234);
    expect($saddle->nonce())->toBeNull();
});
