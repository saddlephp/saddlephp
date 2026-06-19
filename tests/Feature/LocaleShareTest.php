<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

it('shares the locale and panel translations', function () {
    $this->actingAsUser();

    $this->get('/admin')
        ->assertInertia(fn (Assert $page) => $page
            ->where('saddle.locale', 'en')
            ->where('saddle.translations.actions.save', 'Save changes')
        );
});
