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

it('200s the index when require_policy is absent and no policy exists (default convention)', function () {
    // No config()->set at all — the default must remain fail-open.
    $this->actingAsUser();

    $this->get('/admin/resources/horses')->assertOk();
});
