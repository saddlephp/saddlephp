<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->composerPath = base_path('composer.json');
    $this->composerBackup = File::get($this->composerPath);
});

afterEach(function () {
    File::put($this->composerPath, $this->composerBackup);
    File::delete(config_path('saddle.php'));
    File::deleteDirectory(app_path('Saddle'));
    File::deleteDirectory(public_path('vendor/saddle'));
});

it('adds the upgrade hook to composer when confirmed', function () {
    $this->artisan('saddle:install')
        ->expectsConfirmation('Keep panel assets fresh automatically? (adds saddle:upgrade to composer post-update-cmd)', 'yes')
        ->assertSuccessful();

    $composer = json_decode(File::get($this->composerPath), true);

    expect($composer['scripts']['post-update-cmd'])->toContain('@php artisan saddle:upgrade');
});

it('leaves composer untouched when declined', function () {
    $this->artisan('saddle:install')
        ->expectsConfirmation('Keep panel assets fresh automatically? (adds saddle:upgrade to composer post-update-cmd)', 'no')
        ->assertSuccessful();

    expect(File::get($this->composerPath))->toBe($this->composerBackup);
});

it('does not duplicate the hook when run twice', function () {
    $this->artisan('saddle:install')
        ->expectsConfirmation('Keep panel assets fresh automatically? (adds saddle:upgrade to composer post-update-cmd)', 'yes')
        ->assertSuccessful();

    $this->artisan('saddle:install')->assertSuccessful();

    $composer = json_decode(File::get($this->composerPath), true);
    $hooks = array_filter(
        $composer['scripts']['post-update-cmd'],
        fn ($hook) => $hook === '@php artisan saddle:upgrade',
    );

    expect($hooks)->toHaveCount(1);
});
