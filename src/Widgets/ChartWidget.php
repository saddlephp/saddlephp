<?php

declare(strict_types=1);

namespace SaddlePHP\Widgets;

use Illuminate\Http\Request;

abstract class ChartWidget extends Widget
{
    protected string $component = 'chart-widget';

    abstract public function heading(): string;

    /** @return array<string, int|float> ordered label => value */
    abstract public function data(Request $request): array;

    public function toArray(Request $request): array
    {
        $data = $this->data($request);

        return [
            'component' => $this->component,
            'heading' => $this->heading(),
            'labels' => array_keys($data),
            'values' => array_values($data),
        ];
    }
}
