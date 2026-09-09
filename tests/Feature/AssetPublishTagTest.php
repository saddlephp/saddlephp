<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use SaddlePHP\SaddleServiceProvider;

/**
 * The compiled bundle must publish under `laravel-assets` as well as
 * `saddle-assets`.
 *
 * `laravel-assets` is what a fresh Laravel application's own composer.json
 * calls on post-update-cmd -- `vendor:publish --tag=laravel-assets --force` --
 * and it is the only thing that republishes a package's compiled frontend
 * automatically on `composer update`.
 *
 * Without it, upgrading Saddle updated the PHP and left public/vendor/saddle/
 * holding the previous bundle. Every server-side check passed while the panel
 * rendered the old frontend, so the symptom was "the feature I upgraded for
 * isn't there" with nothing pointing at the cause. It cost a consumer a bad
 * deploy on 1.3.0 -> 1.4.0.
 */
afterEach(function () {
    File::deleteDirectory(public_path('vendor/saddle'));
});

/** The registered source paths, resolved so the assertions are path-shape agnostic. */
function publishedUnder(string $tag): array
{
    $paths = ServiceProvider::pathsToPublish(SaddleServiceProvider::class, $tag);

    return collect($paths)->mapWithKeys(fn (string $to, string $from) => [realpath($from) => $to])->all();
}

it('publishes the compiled bundle under the laravel-assets convention', function () {
    expect(publishedUnder('laravel-assets'))
        ->toHaveKey(realpath(__DIR__.'/../../dist'), public_path('vendor/saddle'));
});

it('keeps publishing under saddle-assets, so saddle:upgrade is unchanged', function () {
    expect(publishedUnder('saddle-assets'))->toHaveKey(realpath(__DIR__.'/../../dist'));
});

/**
 * `laravel-assets` runs unattended on every composer update, so it must carry
 * the bundle and NOTHING else. Publishing config, migrations or language files
 * on that tag would overwrite a host's customised copies without being asked.
 */
it('publishes only the bundle on laravel-assets, never config or lang', function () {
    expect(publishedUnder('laravel-assets'))->toHaveCount(1);
});

it('actually writes the bundle when the laravel-assets tag is published', function () {
    $this->artisan('vendor:publish', ['--tag' => 'laravel-assets', '--force' => true])->assertSuccessful();

    expect(File::exists(public_path('vendor/saddle/manifest.json')))->toBeTrue();
});
