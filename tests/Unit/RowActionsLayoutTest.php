<?php

declare(strict_types=1);

/**
 * A resource index with three row actions wrapped its actions cell. Measured on
 * a table with View / Edit / Test Connection: the cell was 112px wide (a fixed
 * `w-28` on the header) and 73px tall, so the links broke over three lines --
 * "View  Edit", then "Test", then "Connection". A control split across two
 * lines mid-phrase reads as broken rather than as a link, and the tallest cell
 * sets the row height for every row in the table.
 *
 * There is no JS test runner in this package, so the layout contract is pinned
 * at the source, the same way VersionParityTest pins vite.config.js.
 */
function panelSource(string $path): string
{
    return (string) file_get_contents(dirname(__DIR__, 2).'/resources/js/'.$path);
}

it('sizes the actions column to its content instead of a fixed width', function () {
    $index = panelSource('Pages/Resources/Index.vue');

    expect($index)->toContain('<th class="w-px"></th>')
        ->and($index)->not->toContain('w-28');
});

it('keeps row action links on one line', function () {
    expect(panelSource('Pages/Resources/Index.vue'))
        ->toContain('<td class="whitespace-nowrap px-4 py-3 text-right text-xs">');

    expect(panelSource('Components/RelationManagerTable.vue'))
        ->toContain('<td class="whitespace-nowrap px-2 py-2 text-right">');
});

/**
 * nowrap without a scroll container would trade a wrapped cell for an
 * unreachable one: the rounded card is `overflow-hidden`, so anything past its
 * width is clipped rather than scrolled. A table wide enough to overflow was
 * already clipped before this change; it now scrolls.
 */
it('lets a table wider than its card scroll rather than be clipped', function () {
    expect(panelSource('Pages/Resources/Index.vue'))
        ->toContain('<div class="overflow-x-auto">');
});

/**
 * The measured table had three actions and is fine on one line; collapsing into
 * a menu is a different change (focus management, keyboard, click-outside, a
 * portal to escape the scroll container) and is not what a wrapping cell needs.
 * If that ever lands, this assertion is the thing to revisit.
 */
it('still renders every row action inline', function () {
    expect(panelSource('Pages/Resources/Index.vue'))
        ->toContain('v-for="action in actions"');
});
