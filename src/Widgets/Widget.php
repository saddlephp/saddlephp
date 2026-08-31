<?php

declare(strict_types=1);

namespace SaddlePHP\Widgets;

use Illuminate\Http\Request;

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

    /** Per-widget visibility gate. Override to hide for a request/user. */
    public static function canSee(Request $request): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    abstract public function toArray(Request $request): array;
}
