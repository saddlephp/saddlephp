<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
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

        $shared = [
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
        ];

        $user = $request->user();

        if ($user !== null && in_array(Notifiable::class, class_uses_recursive($user), true)) {
            $shared['notifications'] = [
                'unread' => $user->unreadNotifications()->count(),
                'items' => $user->notifications()->latest()->limit(10)->get()->map(fn ($n) => [
                    'id' => $n->id,
                    'message' => (string) ($n->data['message'] ?? $n->type),
                    'url' => $n->data['url'] ?? null,
                    'read' => $n->read_at !== null,
                    'at' => $n->created_at?->diffForHumans(),
                ])->all(),
            ];
        }

        if ($saddle->tenant() !== null) {
            $shared['tenant'] = $this->tenant($saddle);
            $shared['tenants'] = $this->tenants($saddle, $request);
        }

        return array_merge(parent::share($request), ['saddle' => $shared]);
    }

    /** @return array{key: mixed, label: string} */
    protected function tenant(Saddle $saddle): array
    {
        $tenant = $saddle->tenant();

        return [
            'key' => $tenant->getRouteKey(),
            'label' => (string) ($tenant->name ?? $tenant->getRouteKey()),
        ];
    }

    /** @return array<int, array{key: mixed, label: string}> */
    protected function tenants(Saddle $saddle, Request $request): array
    {
        $model = $saddle->tenancyModel();
        $relationship = (string) config('saddle.tenancy.relationship', 'users');
        $userKey = $request->user()?->getKey();

        return $model::whereHas($relationship, fn ($query) => $query->whereKey($userKey))
            ->get()
            ->map(fn ($tenant) => [
                'key' => $tenant->getRouteKey(),
                'label' => (string) ($tenant->name ?? $tenant->getRouteKey()),
            ])
            ->values()->all();
    }
}
