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

    /** Per-widget visibility gate. Override to hide for a request/user. */
    public static function canSee(Request $request): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    abstract public function toArray(Request $request): array;
}
