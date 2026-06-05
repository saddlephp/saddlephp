<?php

declare(strict_types=1);

namespace SaddlePHP;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SaddlePHP\Support\ResourceDiscovery;

class Saddle
{
    public const VERSION = '0.6.0';

    /** @var array<int, class-string<resource>> */
    protected array $registered = [];

    /** @var array<int, class-string<resource>>|null */
    protected ?array $discovered = null;

    /** @var array<int, string> */
    protected array $scripts = [];

    /** @var array<int, string> */
    protected array $styles = [];

    /** The tenant resolved for the current request, or null when tenancy is off. */
    protected ?Model $tenant = null;

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

    public function greeting(): string
    {
        return "Saddle up, cowboy. There's a new admin panel in town.";
    }

    /** @param array<int, class-string<resource>> $resources */
    public function register(array $resources): static
    {
        $this->registered = array_values(array_unique(array_merge($this->registered, $resources)));

        return $this;
    }

    /** @return Collection<int, class-string<resource>> */
    public function resources(): Collection
    {
        if ($this->registered !== []) {
            return collect($this->registered);
        }

        $this->discovered ??= ResourceDiscovery::in(
            config('saddle.resources.path', app_path('Saddle')),
            config('saddle.resources.namespace', 'App\\Saddle'),
        );

        return collect($this->discovered);
    }

    /** @return class-string<resource>|null */
    public function resourceFor(string $uriKey): ?string
    {
        return $this->resources()->first(fn (string $resource) => $resource::uriKey() === $uriKey);
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

    /** @return array<int, array{group: string|null, items: array<int, array<string, mixed>>}> */
    public function nav(Request $request): array
    {
        return $this->resources()
            ->filter(fn (string $resource) => $resource::allows('viewAny'))
            ->groupBy(fn (string $resource) => $resource::$group ?? '')
            ->map(fn (Collection $resources, string $group) => [
                'group' => $group === '' ? null : $group,
                'items' => $resources->map(fn (string $resource) => [
                    'label' => $resource::label(),
                    'uriKey' => $resource::uriKey(),
                    'icon' => $resource::$icon,
                    'active' => $request->is($this->path().'/resources/'.$resource::uriKey().'*'),
                ])->values()->all(),
            ])
            ->values()->all();
    }
}
