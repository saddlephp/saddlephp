<?php

declare(strict_types=1);

namespace RodeoPHP\Support;

class AssetManifest
{
    protected const ENTRY = 'resources/js/app.js';

    /** @return array<string, mixed>|null */
    public static function manifest(): ?array
    {
        $path = public_path('vendor/rodeo/manifest.json');

        if (! is_file($path)) {
            return null;
        }

        return json_decode((string) file_get_contents($path), true) ?: null;
    }

    public static function hash(): ?string
    {
        $path = public_path('vendor/rodeo/manifest.json');

        return is_file($path) ? md5_file($path) : null;
    }

    public static function script(): ?string
    {
        $entry = static::manifest()[self::ENTRY] ?? null;

        return $entry ? asset('vendor/rodeo/'.$entry['file']) : null;
    }

    /** @return array<int, string> */
    public static function styles(): array
    {
        $entry = static::manifest()[self::ENTRY] ?? null;

        return collect($entry['css'] ?? [])
            ->map(fn (string $file) => asset('vendor/rodeo/'.$file))
            ->all();
    }
}
