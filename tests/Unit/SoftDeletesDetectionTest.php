<?php

declare(strict_types=1);

use Workbench\App\Saddle\HorseResource;
use Workbench\App\Saddle\RiderResource;

it('detects a soft-deletable model', function () {
    expect(HorseResource::usesSoftDeletes())->toBeTrue()
        ->and(RiderResource::usesSoftDeletes())->toBeFalse();
});
