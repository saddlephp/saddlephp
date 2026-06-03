<?php

declare(strict_types=1);

namespace RodeoPHP;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RodeoPHP\Support\ResourceDiscovery;

class Rodeo
{
    public const VERSION = '0.1.0-dev';

    /** @var array<int, class-string<resource>> */
    protected array $registered = [];

    /** @var array<int, class-string<resource>>|null */
    protected ?array $discovered = null;

    public function version(): string
    {
        return self::VERSION;
    }

    public function greeting(): string
    {
        return "Saddle up, cowboy. There's a new CMF in town.";
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
            config('rodeo.resources.path', app_path('Rodeo')),
            config('rodeo.resources.namespace', 'App\\Rodeo'),
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
        return trim((string) config('rodeo.path', 'admin'), '/');
    }

    /** @return array<int, array{group: string|null, items: array<int, array<string, mixed>>}> */
    public function nav(Request $request): array
    {
        return $this->resources()
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
