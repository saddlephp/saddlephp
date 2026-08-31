<?php

declare(strict_types=1);

namespace SaddlePHP\Widgets;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use SaddlePHP\Resource;
use SaddlePHP\Saddle;

abstract class Widget
{
    /** Ascending order on the dashboard. */
    public static int $sort = 0;

    /** Frontend component discriminator. Subclasses set this. */
    protected string $component;

    /**
     * The frontend component key this widget dispatches on.
     *
     * Public so a plugin can name the key it registers against via
     * `window.Saddle.registerWidget(key, Component)`. Without both halves the
     * abstract Widget base was effectively closed to subclassing outside
     * StatWidget and ChartWidget, because the renderer's map had no entry for
     * anything else.
     */
    public function component(): string
    {
        return $this->component;
    }

    /**
     * The resource whose policy gates this widget, e.g. `HorseResource::class`.
     *
     * An aggregate discloses more than it looks like it does: a competitor
     * tenant learns your customer count, order volume and growth rate from a
     * dashboard tile. Pointing a widget at the resource whose data it summarises
     * means one declaration gates the tile with the same policy that gates the
     * table.
     *
     * @var class-string<\SaddlePHP\Resource>|null
     */
    public static ?string $resource = null;

    /**
     * Per-widget visibility gate.
     *
     * Defaults to the declared resource's `viewAny`. A widget that declares no
     * resource cannot be authorized by anything, so it is hidden rather than
     * shown to every authenticated panel user -- set
     * `saddle.authorization.require_widget_resource` to false for the old
     * fail-open behaviour, or override this method to opt a genuinely public
     * widget back in.
     */
    public static function canSee(Request $request): bool
    {
        $resource = static::$resource;

        if ($resource !== null) {
            return $resource::allows('viewAny');
        }

        return ! config('saddle.authorization.require_widget_resource', true);
    }

    /**
     * Confine a widget's query to the bound tenant.
     *
     * Widgets query models directly, so `Resource::query()`'s tenant scoping is
     * never involved and every widget had to hand-roll this. A no-op when
     * tenancy is off, when no tenant is bound, or when the declared resource is
     * global by design.
     *
     * @template TQuery of Builder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    protected function scopeToTenant(Builder $query, ?string $relation = null): Builder
    {
        $tenant = app(Saddle::class)->tenant();

        if ($tenant === null) {
            return $query;
        }

        $resource = static::$resource;
        $relation ??= $resource !== null ? $resource::$tenant : null;

        if ($relation === null) {
            return $query;
        }

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query->whereBelongsTo($tenant, $relation);

        return $query;
    }

    /** @return array<string, mixed> */
    abstract public function toArray(Request $request): array;
}
