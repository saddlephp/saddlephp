<?php

declare(strict_types=1);

use SaddlePHP\Saddle;

it('boots the service provider and resolves the manager', function () {
    expect(app(Saddle::class))->toBeInstanceOf(Saddle::class)
        ->and(config('saddle.path'))->toBe('admin');
});
