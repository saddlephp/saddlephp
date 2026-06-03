<?php

declare(strict_types=1);

use RodeoPHP\Support\ResourceDiscovery;
use RodeoPHP\Tests\Fixtures\Discovery\PonyResource;

it('discovers concrete resource subclasses only', function () {
    $found = ResourceDiscovery::in(
        __DIR__.'/../Fixtures/Discovery',
        'RodeoPHP\\Tests\\Fixtures\\Discovery',
    );

    expect($found)->toBe([PonyResource::class]);
});

it('returns empty for a missing directory', function () {
    expect(ResourceDiscovery::in(__DIR__.'/nope', 'Nope'))->toBe([]);
});
