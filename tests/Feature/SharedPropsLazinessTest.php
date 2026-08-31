<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Workbench\App\Models\Horse;

/**
 * Count the queries a request runs, ignoring the ones the test harness itself
 * needs (session and the authenticated user lookup).
 */
function queriesDuring(callable $callback): array
{
    $queries = [];

    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query->sql;
    });

    $callback();

    return $queries;
}

beforeEach(function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'H1']);
});

it('does not query notifications for the global search endpoint', function () {
    // Global search returns JSON, never the Inertia shell, so nothing it
    // returns can use the shared props the middleware computes.
    $queries = queriesDuring(fn () => $this->getJson('/admin/resources/search?q=H1')->assertOk());

    expect(collect($queries)->filter(fn ($sql) => str_contains($sql, 'notifications')))
        ->toBeEmpty();
});

it('keeps the global search endpoint to the tables it actually searches', function () {
    $queries = queriesDuring(fn () => $this->getJson('/admin/resources/search?q=H1')->assertOk());

    // Every query should be against a searched resource table, never the
    // middleware's notifications or the tenant membership lookup.
    $strays = collect($queries)->reject(
        fn ($sql) => str_contains($sql, 'horses')
            || str_contains($sql, 'riders')
            || str_contains($sql, 'ranches')
            || str_contains($sql, 'users')
    );

    expect($strays->all())->toBe([]);
});

it('still shares notifications on a real inertia page', function () {
    $queries = queriesDuring(fn () => $this->get('/admin/resources/horses')->assertOk());

    expect(collect($queries)->filter(fn ($sql) => str_contains($sql, 'notifications')))
        ->not->toBeEmpty();
});
