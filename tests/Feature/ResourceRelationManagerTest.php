<?php

declare(strict_types=1);

use SaddlePHP\Saddle;
use Workbench\App\Models\Ranch;
use Workbench\App\Saddle\RanchResource;
use Workbench\App\Saddle\RelationManagers\HorsesRelationManager;

beforeEach(function () {
    $this->app->make(Saddle::class)->register([RanchResource::class]);
});

it('registers relation managers on a resource', function () {
    expect(RanchResource::relations())->toBe([HorsesRelationManager::class]);
});

it('resolves the ranch resource by uri key', function () {
    expect(app(Saddle::class)->resourceFor('ranches'))->toBe(RanchResource::class);
});
