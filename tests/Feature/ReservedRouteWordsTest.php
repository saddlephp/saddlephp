<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('never lets a record route capture the reserved create word', function () {
    $this->actingAsUser();

    // With the {record} constraint in place, 'create' can never be captured as
    // a record key, so /resources/horses/create/edit matches no route (404)
    // rather than mistaking 'create' for a record and 500ing on lookup.
    $this->get('/admin/resources/horses/create/edit')->assertNotFound();
});

it('never lets a record route capture the reserved options word', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/horses/options/edit')->assertNotFound();
});

it('constrains every {record} route so reserved words cannot match', function () {
    $recordRoutes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => in_array('record', $route->parameterNames(), true));

    // edit, update, destroy — every route that carries {record}.
    expect($recordRoutes)->toHaveCount(3);

    $recordRoutes->each(function ($route): void {
        $pattern = $route->wheres['record'] ?? null;

        expect($pattern)->not->toBeNull()
            ->and(preg_match('/'.$pattern.'/', 'create'))->toBe(0)
            ->and(preg_match('/'.$pattern.'/', 'options'))->toBe(0)
            ->and(preg_match('/'.$pattern.'/', '42'))->toBe(1)
            ->and(preg_match('/'.$pattern.'/', 'a-slug'))->toBe(1);
    });
});
