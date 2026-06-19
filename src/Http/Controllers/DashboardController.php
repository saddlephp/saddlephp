<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use SaddlePHP\Saddle;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $widgets = app(Saddle::class)->widgets()
            ->filter(fn (string $widget) => $widget::canSee($request))
            // One broken widget must not break the dashboard — build defensively.
            ->map(fn (string $widget) => rescue(fn () => (new $widget)->toArray($request), null, report: true))
            ->filter()
            ->values()
            ->all();

        return Inertia::render('Dashboard', ['widgets' => $widgets]);
    }
}
