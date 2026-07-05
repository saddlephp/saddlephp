<?php

declare(strict_types=1);

// HorseResource's model has no policy registered in the base test case
// (Gate::guessPolicyNamesUsing returns null), so allows() takes its no-policy
// branch. These tests pin both sides of the opt-in fail-closed contract.
//
// config('saddle.authorization.require_policy') is read inside allows() at
// request time, so a per-test config()->set before the request is enough — no
// defineEnvironment override needed.

it('403s the index for an authenticated user when require_policy is on and no policy exists', function () {
    config()->set('saddle.authorization.require_policy', true);

    $this->actingAsUser();

    $this->get('/admin/resources/horses')->assertForbidden();
});

it('200s the index when require_policy is off and no policy exists (default convention)', function () {
    config()->set('saddle.authorization.require_policy', false);

    $this->actingAsUser();

    $this->get('/admin/resources/horses')->assertOk();
});

it('follows the workbench fail-open opt-out when require_policy is not overridden', function () {
    // The workbench opts into fail-open (WorkbenchServiceProvider) because its
    // demo resources have no policies, so with no per-test override the index
    // stays open.
    $this->actingAsUser();

    $this->get('/admin/resources/horses')->assertOk();
});

it('ships a fail-closed default in the package config', function () {
    $config = require dirname(__DIR__, 2).'/config/saddle.php';

    // Secure by default: production installs deny policy-less resources.
    expect($config['authorization']['require_policy'])->toBeTrue();
});
