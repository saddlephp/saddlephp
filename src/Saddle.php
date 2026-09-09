<?php

declare(strict_types=1);

namespace SaddlePHP;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SaddlePHP\Support\ResourceDiscovery;
use SaddlePHP\Support\WidgetDiscovery;
use SaddlePHP\Tenancy\RegistersTenants;
use SaddlePHP\Widgets\Widget;

class Saddle
{
    public const VERSION = '1.5.0';

    /** @var array<int, class-string<\SaddlePHP\Resource>> */
    protected array $registered = [];

    /** @var array<int, class-string<\SaddlePHP\Resource>>|null */
    protected ?array $discovered = null;

    /** @var array<int, string> */
    protected array $scripts = [];

    /** @var array<int, string> */
    protected array $styles = [];

    /** @var array<int, Closure> Callbacks contributing extra shared Inertia props. */
    protected array $sharing = [];

    /** Transform the computed navigation before it is shared, or null for none. */
    protected ?Closure $navUsing = null;

    /** Resolve the CSP nonce for the current request, or null for none. */
    protected ?Closure $nonceUsing = null;

    /** @var array<int, string> Extra theme tokens registered by plugins/hosts. */
    protected array $extraThemeTokens = [];

    /** The tenant resolved for the current request, or null when tenancy is off. */
    protected ?Model $tenant = null;

    /**
     * Register a callback contributing extra keys to the shared `saddle` Inertia
     * prop (e.g. a plugin exposing its own frontend data). Core keys always win.
     *
     * @param  Closure(Request): array<string, mixed>  $callback
     */
    public function sharing(Closure $callback): static
    {
        $this->sharing[] = $callback;

        return $this;
    }

    /**
     * The merged extra props from every registered sharing callback. Each callback
     * is guarded like nav(): one throwing (or non-array-returning) plugin callback
     * contributes nothing rather than 500-ing every panel page.
     *
     * @return array<string, mixed>
     */
    public function sharedProps(Request $request): array
    {
        return collect($this->sharing)->reduce(function (array $carry, Closure $callback) use ($request) {
            $extra = rescue(fn () => $callback($request), [], report: true);

            return array_merge($carry, is_array($extra) ? $extra : []);
        }, []);
    }

    /**
     * Transform the computed navigation array (reorder, filter, or append custom
     * links) before it is shared with the frontend. The callback receives the nav
     * groups and the request and returns the new list; the result is re-indexed
     * with array_values(), so any top-level string keys are dropped. Each item
     * should carry the keys {label, uriKey, icon, active}.
     *
     * @param  Closure(array<int, array{group: string|null, items: array<int, array<string, mixed>>}>, Request): array<int, mixed>  $callback
     */
    public function navUsing(Closure $callback): static
    {
        $this->navUsing = $callback;

        return $this;
    }

    /**
     * Supply the Content-Security-Policy nonce for the panel shell's one inline
     * script (the pre-paint dark-mode bootstrap).
     *
     * The package cannot know an application's nonce convention -- a container
     * binding, `Vite::cspNonce()`, a value stashed on the request by middleware
     * -- so it takes a callback instead of a value:
     *
     *     Saddle::resolveNonceUsing(fn () => app('csp.nonce'));
     *
     * @param  Closure(): mixed  $callback
     */
    public function resolveNonceUsing(Closure $callback): static
    {
        $this->nonceUsing = $callback;

        return $this;
    }

    /**
     * The nonce for the current request, or null when the host has registered
     * none (the default, which leaves the rendered shell byte-identical).
     *
     * Called once per shell render rather than memoized: a nonce reused across
     * requests is not a nonce, and on a long-lived server (Octane) the manager
     * is a singleton that outlives the request.
     *
     * Failures are swallowed on purpose -- and, unlike nav() and sharedProps(),
     * not even reported. A misconfigured nonce provider must not 500 every page
     * of the panel; without a nonce the browser refuses one inline script and
     * the panel still works, minus the pre-paint theme class. A provider that
     * throws on every request would otherwise flood the log from the shell.
     */
    public function nonce(): ?string
    {
        if ($this->nonceUsing === null) {
            return null;
        }

        $nonce = rescue(fn () => ($this->nonceUsing)(), null, report: false);

        return is_string($nonce) && $nonce !== '' ? $nonce : null;
    }

    /**
     * Allow additional theme tokens beyond the built-in set, so a plugin can
     * expose its own CSS custom properties (injected as `--color-<token>`).
     * Token names are constrained to a safe CSS-identifier pattern.
     */
    public function registerThemeTokens(string ...$tokens): static
    {
        foreach ($tokens as $token) {
            if (preg_match('/^[a-z][a-z0-9-]*$/D', $token) === 1 && ! in_array($token, $this->extraThemeTokens, true)) {
                $this->extraThemeTokens[] = $token;
            }
        }

        return $this;
    }

    /** Queue a plugin script for the panel shell. Developer-supplied URLs only. */
    public function script(string $url): static
    {
        if (! in_array($url, $this->scripts, true)) {
            $this->scripts[] = $url;
        }

        return $this;
    }

    /** Queue a plugin stylesheet for the panel shell. */
    public function style(string $url): static
    {
        if (! in_array($url, $this->styles, true)) {
            $this->styles[] = $url;
        }

        return $this;
    }

    /** @return array<int, string> */
    public function scripts(): array
    {
        return $this->scripts;
    }

    /** @return array<int, string> */
    public function styles(): array
    {
        return $this->styles;
    }

    public function version(): string
    {
        return self::VERSION;
    }

    /**
     * The dashboard's headline, or null to keep Saddle's own wording.
     *
     * This used to return a fixed cowboy one-liner that nothing rendered:
     * Dashboard.vue hard-coded its own "Howdy{name}." instead, so an
     * application could not change the first line of its own admin panel
     * without a JavaScript shim that string-matched the default text in the
     * DOM. The configured string may contain `:name`.
     *
     * With no name to interpolate the placeholder is removed *with its leading
     * separator*, because a plain str_replace() leaves the punctuation
     * stranded: "Welcome, :name." renders "Welcome, ." on the first screen a
     * signed-out panel shows. A separator *following* a leading `:name` is
     * deliberately left alone -- stripping it would mangle "Howdy :name," which
     * is the commoner shape by far.
     */
    public function greeting(?string $name = null): ?string
    {
        $greeting = config('saddle.brand.greeting');

        if (! is_string($greeting) || $greeting === '') {
            return null;
        }

        if ($name !== null && $name !== '') {
            return str_replace(':name', $name, $greeting);
        }

        $greeting = (string) preg_replace('/[,;:]?\s*:name/u', '', $greeting);

        return trim((string) preg_replace('/\s{2,}/u', ' ', $greeting));
    }

    /** The dashboard's second line, or null to keep Saddle's own wording. */
    public function subgreeting(): ?string
    {
        $sub = config('saddle.brand.subgreeting');

        return is_string($sub) && $sub !== '' ? $sub : null;
    }

    /**
     * The brand accent color, validated before it is interpolated raw into the
     * panel's inline <style> block. Only a bare hex value or a single CSS color
     * function (rgb/hsl/oklch) with no structural characters is allowed; any
     * other value (e.g. one trying to close the rule and inject CSS) falls back
     * to the default.
     */
    public function accent(): string
    {
        $default = '#d9501f';
        $accent = config('saddle.brand.accent', $default);

        if (! is_string($accent)) {
            return $default;
        }

        $accent = trim($accent);

        if (preg_match('/^#[0-9a-fA-F]{3,8}$|^(rgb|hsl|oklch)\([^;{}<>]*\)$/D', $accent) === 1) {
            return $accent;
        }

        return $default;
    }

    /**
     * Host theme overrides: a validated map of token => CSS colour, injected into
     * the panel's :root block. Only allowlisted tokens and safe colour values pass.
     *
     * @return array<string, string>
     */
    public function theme(): array
    {
        $allowed = array_merge(
            ['bg', 'surface', 'surface-2', 'ink', 'ink-2', 'ink-3', 'line', 'line-2', 'accent'],
            $this->extraThemeTokens,
        );
        $theme = config('saddle.brand.theme', []);

        if (! is_array($theme)) {
            return [];
        }

        $valid = [];

        foreach ($theme as $token => $value) {
            if (! in_array($token, $allowed, true) || ! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if (preg_match('/^#[0-9a-fA-F]{3,8}$|^(rgb|hsl|oklch)\([^;{}<>]*\)$/D', $value) === 1) {
                $valid[$token] = $value;
            }
        }

        return $valid;
    }

    /** @param array<int, class-string<\SaddlePHP\Resource>> $resources */
    public function register(array $resources): static
    {
        $this->registered = array_values(array_unique(array_merge($this->registered, $resources)));

        return $this;
    }

    /**
     * Every resource the panel knows about: those discovered under the
     * configured path, plus any registered explicitly.
     *
     * These used to be mutually exclusive -- the first register() call anywhere
     * in the boot cycle suppressed discovery entirely. Since plugins register
     * from their service providers, installing any plugin silently emptied the
     * host application's panel: no nav, no global search, 404 on every resource
     * route. Set `saddle.resources.discovery` to false to opt out of scanning
     * and curate the list by hand.
     *
     * @return Collection<int, class-string<\SaddlePHP\Resource>>
     */
    public function resources(): Collection
    {
        $discovered = config('saddle.resources.discovery', true)
            ? $this->discovered ??= ResourceDiscovery::in(
                config('saddle.resources.path', app_path('Saddle')),
                config('saddle.resources.namespace', 'App\\Saddle'),
            )
            : [];

        return collect(array_values(array_unique([...$discovered, ...$this->registered])));
    }

    /** @return class-string<\SaddlePHP\Resource>|null */
    public function resourceFor(string $uriKey): ?string
    {
        return $this->resources()->first(fn (string $resource) => $resource::uriKey() === $uriKey);
    }

    /**
     * The registered resource managing a model class, if there is one.
     *
     * Used to authorize a relation lookup against the resource that owns the
     * rows being returned, rather than the resource being edited.
     *
     * @param  class-string<Model>  $model
     * @return class-string<\SaddlePHP\Resource>|null
     */
    public function resourceForModel(string $model): ?string
    {
        return $this->resources()->first(fn (string $resource) => $resource::$model === $model);
    }

    /** @var array<int, class-string<Widget>> */
    protected array $registeredWidgets = [];

    /** @param array<int, class-string<Widget>> $widgets */
    public function registerWidgets(array $widgets): static
    {
        $this->registeredWidgets = array_values(array_unique(array_merge($this->registeredWidgets, $widgets)));

        return $this;
    }

    /** @var array<int, class-string<Widget>>|null */
    protected ?array $discoveredWidgets = null;

    /**
     * Discovered widgets plus explicitly registered ones, for the same reason
     * resources() merges rather than choosing. Memoized to match, so repeated
     * calls do not re-glob and re-reflect the widget directory.
     *
     * @return Collection<int, class-string<Widget>>
     */
    public function widgets(): Collection
    {
        $discovered = config('saddle.widgets.discovery', true)
            ? $this->discoveredWidgets ??= WidgetDiscovery::in(
                config('saddle.widgets.path', app_path('Saddle/Widgets')),
                config('saddle.widgets.namespace', 'App\\Saddle\\Widgets'),
            )
            : [];

        return collect(array_values(array_unique([...$discovered, ...$this->registeredWidgets])));
    }

    public function path(): string
    {
        $path = trim((string) config('saddle.path', 'admin'), '/');

        if ($this->tenant !== null) {
            return $path.'/'.$this->tenant->getRouteKey();
        }

        return $path;
    }

    /** The configured tenant model class, or null when tenancy is disabled. */
    public function tenancyModel(): ?string
    {
        return config('saddle.tenancy.model');
    }

    /** The configured tenancy access gate (an invokable), or null. */
    public function tenantGate(): ?callable
    {
        $gate = config('saddle.tenancy.gate');

        return $gate === null ? null : app($gate);
    }

    /** Whether tenant self-registration is enabled. */
    public function canRegisterTenant(): bool
    {
        return config('saddle.tenancy.registration') !== null;
    }

    /** The configured tenant registration handler, or null. */
    public function tenantRegistration(): ?RegistersTenants
    {
        $handler = config('saddle.tenancy.registration');

        return $handler === null ? null : app($handler);
    }

    /** Bind the tenant resolved for the current request. */
    public function useTenant(Model $tenant): static
    {
        $this->tenant = $tenant;

        return $this;
    }

    /** The tenant bound for the current request, or null when tenancy is off. */
    public function tenant(): ?Model
    {
        return $this->tenant;
    }

    /**
     * Drop the bound tenant. On long-lived servers (Octane) the container
     * singleton survives across requests, so the tenant must be reset between
     * them or one request's tenant would leak into the next.
     */
    public function forgetTenant(): void
    {
        $this->tenant = null;
    }

    /** @return array<int, array{group: string|null, items: array<int, array<string, mixed>>}> */
    public function nav(Request $request): array
    {
        $nav = $this->resources()
            ->filter(fn (string $resource) => $resource::allows('viewAny'))
            ->groupBy(fn (string $resource) => $resource::$group ?? '')
            ->map(fn (Collection $resources, string $group) => [
                'group' => $group === '' ? null : $group,
                // One broken resource (a throwing label/uriKey/etc.) must not take
                // down the whole sidebar: build each item defensively, report the
                // failure, and drop only that item while the group stands.
                'items' => $resources->map(fn (string $resource) => rescue(fn () => [
                    'label' => $resource::label(),
                    'uriKey' => $resource::uriKey(),
                    'icon' => $resource::$icon,
                    'active' => $request->is($this->path().'/resources/'.$resource::uriKey().'*'),
                ], null, report: true))->filter()->values()->all(),
            ])
            ->values()->all();

        if ($this->navUsing === null) {
            return $nav;
        }

        // Guard the transform the same way each nav item is guarded: a throwing
        // (or non-array) navUsing callback falls back to the untransformed nav
        // rather than 500-ing every panel page.
        $transformed = rescue(fn () => ($this->navUsing)($nav, $request), $nav, report: true);

        return array_values(is_array($transformed) ? $transformed : $nav);
    }
}
