<?php

declare(strict_types=1);

use SaddlePHP\Support\Search;

it('escapes LIKE wildcards so they match literally', function () {
    expect(Search::escapeLike('50%'))->toBe('50\%')
        ->and(Search::escapeLike('a_b'))->toBe('a\_b')
        ->and(Search::escapeLike('trail\\'))->toBe('trail\\\\');
});

it('leaves a plain term untouched', function () {
    expect(Search::escapeLike('Cisco'))->toBe('Cisco')
        ->and(Search::escapeLike(''))->toBe('');
});
