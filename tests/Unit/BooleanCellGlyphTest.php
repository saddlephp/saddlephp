<?php

declare(strict_types=1);

/**
 * `false` used to render the same em dash as "no value".
 *
 * Measured on an integrations table: `true` rendered a blue check and `false`
 * rendered `—`, so the pair read as "yes / unknown" rather than "yes / no". In a
 * panel where `—` already means "not measured" — which is StatWidget's own
 * convention for a null value — "switched off" and "we have no idea" rendered
 * identically, and those are different facts.
 *
 * `false` now gets a muted cross. The em dash is reserved for a boolean cell
 * with no value at all, which `BooleanColumn` never produces on its own (it
 * casts) but a `formatUsing()` callback returning null now can.
 *
 * There is no JS test runner in this package, so the contract is pinned at the
 * source, the same way VersionParityTest pins vite.config.js.
 */
function booleanCellSource(string $path): string
{
    return (string) file_get_contents(dirname(__DIR__, 2).'/resources/js/'.$path);
}

it('renders false as a cross rather than the null em dash', function () {
    $index = booleanCellSource('Pages/Resources/Index.vue');

    expect($index)->toContain("v-else-if=\"column.type === 'boolean' && row.cells[column.name] != null\"")
        ->and($index)->toContain('<path d="M18 6 6 18M6 6l12 12" />');
});

it('keeps the em dash for a boolean cell with no value', function () {
    expect(booleanCellSource('Pages/Resources/Index.vue'))
        ->toContain("<span v-else-if=\"column.type === 'boolean'\" :aria-label=\"t('booleans.unknown')\" class=\"text-ink-3\">&mdash;</span>");
});

it('applies the same distinction on the read-only view page', function () {
    $display = booleanCellSource('Components/DisplayEntry.vue');

    // The first branch already caught a null/empty display; only the false
    // branch shared the dash, and it no longer does.
    expect($display)->toContain('<path d="M18 6 6 18M6 6l12 12" />')
        ->and(substr_count($display, '&mdash;'))->toBe(1);
});

it('labels every boolean cell state through the translation catalogue', function () {
    $index = booleanCellSource('Pages/Resources/Index.vue');
    $display = booleanCellSource('Components/DisplayEntry.vue');

    foreach (['yes', 'no', 'unknown'] as $state) {
        expect($index.$display)->toContain("t('booleans.{$state}')");
    }

    // A new user-visible string that only exists in English is a regression in
    // a package that ships fourteen locales; LanguageParityTest enforces the
    // rest.
    expect($index)->not->toContain('aria-label="Yes"')
        ->and($index)->not->toContain('aria-label="No"')
        ->and($display)->not->toContain('aria-label="Yes"')
        ->and($display)->not->toContain('aria-label="No"');
});
