<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->composerPath = base_path('composer.json');
    $this->composerBackup = File::get($this->composerPath);
});

afterEach(function () {
    File::put($this->composerPath, $this->composerBackup);
    File::delete(config_path('rodeo.php'));
    File::deleteDirectory(app_path('Rodeo'));
    File::deleteDirectory(public_path('vendor/rodeo'));
});

it('adds the upgrade hook to composer when confirmed', function () {
    $this->artisan('rodeo:install')
        ->expectsConfirmation('Keep panel assets fresh automatically? (adds rodeo:upgrade to composer post-update-cmd)', 'yes')
        ->assertSuccessful();

    $composer = json_decode(File::get($this->composerPath), true);

    expect($composer['scripts']['post-update-cmd'])->toContain('@php artisan rodeo:upgrade');
});

it('leaves composer untouched when declined', function () {
    $this->artisan('rodeo:install')
        ->expectsConfirmation('Keep panel assets fresh automatically? (adds rodeo:upgrade to composer post-update-cmd)', 'no')
        ->assertSuccessful();

    expect(File::get($this->composerPath))->toBe($this->composerBackup);
});

it('does not duplicate the hook when run twice', function () {
    $this->artisan('rodeo:install')
        ->expectsConfirmation('Keep panel assets fresh automatically? (adds rodeo:upgrade to composer post-update-cmd)', 'yes')
        ->assertSuccessful();

    $this->artisan('rodeo:install')->assertSuccessful();

    $composer = json_decode(File::get($this->composerPath), true);
    $hooks = array_filter(
        $composer['scripts']['post-update-cmd'],
        fn ($hook) => $hook === '@php artisan rodeo:upgrade',
    );

    expect($hooks)->toHaveCount(1);
});
