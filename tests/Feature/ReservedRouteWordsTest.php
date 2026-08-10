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

it('never lets a record route capture the reserved actions word', function () {
    $this->actingAsUser();

    // 405, not 404: /resources/horses/actions/edit genuinely IS the actions
    // endpoint with action="edit", and that route is POST-only. What matters is
    // that {record} did not swallow "actions" and send us to a record lookup.
    $this->get('/admin/resources/horses/actions/edit')->assertStatus(405);
});

/**
 * Assert against the COMPILED regex, not $route->wheres.
 *
 * Symfony's Route::sanitizeRequirement() strips a leading ^ and a trailing $
 * from every requirement before compiling it, so a pattern that looks anchored
 * in wheres is not anchored in the regex that actually routes the request. The
 * previous version of this test read wheres and passed for years while
 * /resources/horses/create/edit matched with record=create.
 */
it('constrains every {record} route so reserved words cannot match', function () {
    $recordRoutes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => in_array('record', $route->parameterNames(), true));

    // Resource: view, edit, update, destroy, restore, force-delete. Relations: index, store, edit, update, destroy.
    expect($recordRoutes)->toHaveCount(11);

    $recordRoutes->each(function ($route): void {
        // toSymfonyRoute() is where the requirement is handed over and
        // sanitized, so compiling it here exercises the same path the router
        // takes. getCompiled() is null until a request matches.
        $regex = $route->toSymfonyRoute()->compile()->getRegex();

        $pathFor = function (string $record) use ($route): string {
            $uri = str_replace('{record}', $record, $route->uri());

            // Anything else in the template gets an innocuous value.
            return '/'.preg_replace('/\{[^}]+\}/', 'x', $uri);
        };

        foreach (['create', 'options', 'actions', 'export', 'import'] as $reserved) {
            expect(preg_match($regex, $pathFor($reserved)))
                ->toBe(0, "'{$reserved}' was captured as a record by {$route->uri()}");
        }

        expect(preg_match($regex, $pathFor('42')))->toBe(1)
            ->and(preg_match($regex, $pathFor('a-slug')))->toBe(1);
    });
});
