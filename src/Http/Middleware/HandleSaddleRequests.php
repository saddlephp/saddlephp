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

    /**
     * Shared props for the panel shell.
     *
     * Inertia\Middleware::handle() calls this unconditionally, before the
     * request is even handled, so anything eager here is paid by every request
     * that passes through the panel -- including the global-search XHR fired on
     * every keystroke pause, the async options picker, the CSV download, and
     * every write that only redirects. None of those render the shell.
     *
     * The expensive keys are therefore closures. Inertia resolves a closure prop
     * only when it actually builds a page response, and filters by partial path
     * BEFORE resolving, so an excluded closure is never invoked at all.
     */
    public function share(Request $request): array
    {
        $saddle = app(Saddle::class);
        $user = $request->user();

        $shared = [
            'name' => config('saddle.brand.name', 'Saddle'),
            'accent' => config('saddle.brand.accent', '#d9501f'),
            'version' => Saddle::VERSION,
            'path' => $saddle->path(),
            'locale' => app()->getLocale(),
            'translations' => trans('saddle::panel'),
            'nav' => fn () => $saddle->nav($request),
            'greeting' => $saddle->greeting($user === null ? null : (string) $user->name),
            'subgreeting' => $saddle->subgreeting(),
            'user' => $user ? [
                'name' => (string) $user->name,
                'email' => (string) $user->email,
            ] : null,
            'flash' => [
                'success' => $request->hasSession() ? $request->session()->get('success') : null,
                'error' => $request->hasSession() ? $request->session()->get('error') : null,
            ],
        ];

        if ($user !== null && in_array(Notifiable::class, class_uses_recursive($user), true)) {
            $shared['notifications'] = fn () => [
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
            $shared['tenants'] = fn () => $this->tenants($saddle, $request);
            $shared['canRegisterTenant'] = $saddle->canRegisterTenant();
        }

        // Plugin/host-contributed props are merged under the core keys, which
        // always win so the documented shape cannot be clobbered.
        $shared = array_merge($saddle->sharedProps($request), $shared);

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
