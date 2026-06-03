<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use SaddlePHP\Support\AssetManifest;

afterEach(function () {
    File::deleteDirectory(public_path('vendor/saddle'));
});

function writeManifest(array $manifest): void
{
    File::ensureDirectoryExists(public_path('vendor/saddle'));
    File::put(public_path('vendor/saddle/manifest.json'), json_encode($manifest));
}

it('returns nulls and empty styles when no manifest is published', function () {
    expect(AssetManifest::manifest())->toBeNull()
        ->and(AssetManifest::hash())->toBeNull()
        ->and(AssetManifest::script())->toBeNull()
        ->and(AssetManifest::styles())->toBe([]);
});

it('resolves script and style urls from the manifest entry', function () {
    writeManifest([
        'resources/js/app.js' => [
            'file' => 'assets/app-abc123.js',
            'css' => ['assets/app-def456.css'],
        ],
    ]);

    expect(AssetManifest::script())->toEndWith('vendor/saddle/assets/app-abc123.js')
        ->and(AssetManifest::styles())->toHaveCount(1)
        ->and(AssetManifest::styles()[0])->toEndWith('vendor/saddle/assets/app-def456.css')
        ->and(AssetManifest::hash())->toBeString();
});

it('handles a manifest without css gracefully', function () {
    writeManifest(['resources/js/app.js' => ['file' => 'assets/app-abc123.js']]);

    expect(AssetManifest::styles())->toBe([])
        ->and(AssetManifest::script())->toEndWith('vendor/saddle/assets/app-abc123.js');
});

it('returns null script for malformed manifest json', function () {
    File::ensureDirectoryExists(public_path('vendor/saddle'));
    File::put(public_path('vendor/saddle/manifest.json'), '{not-json');

    expect(AssetManifest::manifest())->toBeNull()
        ->and(AssetManifest::script())->toBeNull();
});
