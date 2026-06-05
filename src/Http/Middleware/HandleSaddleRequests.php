<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use SaddlePHP\Saddle;
use SaddlePHP\Support\AssetManifest;

class HandleSaddleRequests extends Middleware
{
    protected $rootView = 'saddle::app';

    public function version(Request $request): ?string
    {
        return AssetManifest::hash();
    }

    public function share(Request $request): array
    {
        $saddle = app(Saddle::class);

        return array_merge(parent::share($request), [
            'saddle' => [
                'name' => config('saddle.brand.name', 'Saddle'),
                'accent' => config('saddle.brand.accent', '#d9501f'),
                'version' => Saddle::VERSION,
                'path' => $saddle->path(),
                'nav' => $saddle->nav($request),
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
