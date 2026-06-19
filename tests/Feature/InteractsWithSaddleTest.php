<?php

declare(strict_types=1);

use SaddlePHP\Testing\InteractsWithSaddle;
use Workbench\App\Saddle\HorseResource;

uses(InteractsWithSaddle::class);

it('asserts a resource is registered', function () {
    $this->assertResourceRegistered('horses');
});

it('asserts a resource has a field', function () {
    $this->assertResourceHasField(HorseResource::class, 'name');
    $this->assertResourceMissingField(HorseResource::class, 'nonexistent');
});

it('asserts a resource has a column', function () {
    $this->assertResourceHasColumn(HorseResource::class, 'name');
});

it('returns the serialized form and table', function () {
    expect($this->saddleForm(HorseResource::class))->toBeArray()
        ->and($this->saddleTable(HorseResource::class))->toBeArray();
});
