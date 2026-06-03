<?php

declare(strict_types=1);

namespace RodeoPHP\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use RodeoPHP\Rodeo;
use RodeoPHP\Support\AssetManifest;

class HandleRodeoRequests extends Middleware
{
    protected $rootView = 'rodeo::app';

    public function version(Request $request): ?string
    {
        return AssetManifest::hash();
    }

    public function share(Request $request): array
    {
        $rodeo = app(Rodeo::class);

        return array_merge(parent::share($request), [
            'rodeo' => [
                'name' => config('rodeo.brand.name', 'RodeoPHP'),
                'accent' => config('rodeo.brand.accent', '#d9501f'),
                'version' => Rodeo::VERSION,
                'path' => $rodeo->path(),
                'nav' => $rodeo->nav($request),
                'user' => $request->user() ? [
                    'name' => (string) $request->user()->name,
                    'email' => (string) $request->user()->email,
                ] : null,
                'flash' => [
                    'success' => $request->hasSession() ? $request->session()->get('success') : null,
                    'error' => $request->hasSession() ? $request->session()->get('error') : null,
                ],
            ],
        ]);
    }
}
