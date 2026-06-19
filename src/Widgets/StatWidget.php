<?php

declare(strict_types=1);

namespace SaddlePHP\Widgets;

use Illuminate\Http\Request;

abstract class StatWidget extends Widget
{
    protected string $component = 'stat-widget';

    abstract public function label(): string;

    abstract public function value(Request $request): string|int;

    public function description(Request $request): ?string
    {
        return null;
    }

    /** Optional inline mini bar chart (ordered numbers). Empty = number-only card. */
    public function chart(Request $request): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        return [
            'component' => $this->component,
            'label' => $this->label(),
            'value' => (string) $this->value($request),
            'description' => $this->description($request),
            'chart' => $this->chart($request),
        ];
    }
}
