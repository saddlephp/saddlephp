<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

afterEach(function () {
    File::delete(config_path('saddle.php'));
    File::deleteDirectory(app_path('Saddle'));
    File::deleteDirectory(public_path('vendor/saddle'));
});

it('publishes config and creates the resources directory', function () {
    $this->artisan('saddle:install', ['--no-interaction' => true])->assertSuccessful();

    expect(File::exists(config_path('saddle.php')))->toBeTrue()
        ->and(File::isDirectory(app_path('Saddle')))->toBeTrue();
});

it('republishes assets on upgrade', function () {
    $this->artisan('saddle:upgrade')->assertSuccessful();
});
