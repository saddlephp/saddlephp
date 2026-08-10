<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use SaddlePHP\Support\Csv;

/**
 * neutralize() guarded only is_string(), so anything that stringified
 * dangerously walked straight past it -- and fputcsv() then fatalled on the
 * object mid-stream, after the 200 and Content-Type were already sent, leaving
 * the browser with a truncated file that looked like a success.
 */
it('neutralizes a Stringable, not just a raw string', function () {
    expect(Csv::neutralize(Str::of('=cmd|\' /C calc\'!A0')))->toBe("'=cmd|' /C calc'!A0");
});

it('neutralizes a backed enum whose value is dangerous', function () {
    $enum = DangerousStatus::Negative;

    expect(Csv::neutralize($enum))->toBe("'-pending");
});

it('renders an array cell as json instead of fatalling', function () {
    expect(Csv::neutralize(['a' => 1]))->toBe('{"a":1}');
});

it('renders an object with __toString', function () {
    $object = new class
    {
        public function __toString(): string
        {
            return 'plain';
        }
    };

    expect(Csv::neutralize($object))->toBe('plain');
});

it('leaves scalars and null alone', function () {
    expect(Csv::neutralize(null))->toBeNull()
        ->and(Csv::neutralize(42))->toBe(42)
        ->and(Csv::neutralize(1.5))->toBe(1.5)
        ->and(Csv::neutralize(true))->toBeTrue()
        ->and(Csv::neutralize('safe'))->toBe('safe');
});

// ---------------------------------------------------------------------------
// Round-trip
// ---------------------------------------------------------------------------

/**
 * Decimal columns come back from MySQL and Postgres as PHP strings, so a
 * negative money value is a string starting with '-' -- which neutralize()
 * prefixes. Without denormalize() every export/import cycle corrupted it.
 */
it('round-trips a value that neutralize had to guard', function (string $original) {
    expect(Csv::denormalize(Csv::neutralize($original)))->toBe($original);
})->with([
    'formula' => '=1+2',
    'negative number' => '-12.50',
    'phone number' => '+1 555 0100',
    'handle' => '@saddle',
    'tab' => "\tindented",
]);

it('leaves an ordinary value untouched through a round-trip', function (string $original) {
    expect(Csv::denormalize(Csv::neutralize($original)))->toBe($original);
})->with([
    'plain' => 'Bramble',
    'embedded comma' => 'Bramble, Jr.',
    'embedded quote' => 'He said "neigh"',
    'leading zero' => '007',
    'unicode' => 'Émile Ruë',
]);

/**
 * A value the user genuinely typed with a leading apostrophe must survive.
 * Only a quote that is guarding a dangerous character gets removed.
 */
it('does not strip an apostrophe the user actually meant', function () {
    expect(Csv::denormalize("'tis a fine horse"))->toBe("'tis a fine horse");
});

it('strips a utf-8 bom so the first header still maps', function () {
    expect(Csv::stripBom("\u{FEFF}name"))->toBe('name')
        ->and(Csv::stripBom('name'))->toBe('name');
});

enum DangerousStatus: string
{
    case Negative = '-pending';
}
