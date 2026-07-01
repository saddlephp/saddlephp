<?php

declare(strict_types=1);

use SaddlePHP\Support\Csv;

it('neutralizes values that would be read as formulas', function () {
    expect(Csv::neutralize('=SUM(A1:A2)'))->toBe("'=SUM(A1:A2)")
        ->and(Csv::neutralize('+1'))->toBe("'+1")
        ->and(Csv::neutralize('-cmd|calc'))->toBe("'-cmd|calc")
        ->and(Csv::neutralize('@ref'))->toBe("'@ref")
        ->and(Csv::neutralize("\tlead"))->toBe("'\tlead");
});

it('leaves safe scalar values untouched', function () {
    expect(Csv::neutralize('Cisco'))->toBe('Cisco')
        ->and(Csv::neutralize(''))->toBe('')
        ->and(Csv::neutralize(42))->toBe(42)
        ->and(Csv::neutralize(null))->toBeNull();
});
