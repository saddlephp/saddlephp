<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use SaddlePHP\Widgets\ChartWidget;
use SaddlePHP\Widgets\StatWidget;

class DefaultsOnlyStatWidget extends StatWidget
{
    public function label(): string
    {
        return 'Riders';
    }

    public function value(Request $request): string|int
    {
        return 42;
    }
}

class EmptyDataChartWidget extends ChartWidget
{
    public function heading(): string
    {
        return 'Signups';
    }

    public function data(Request $request): array
    {
        return [];
    }
}

it('serializes a number-only stat widget with a null description and no chart', function () {
    expect((new DefaultsOnlyStatWidget)->toArray(new Request))->toBe([
        'component' => 'stat-widget',
        'label' => 'Riders',
        'value' => '42',
        'description' => null,
        'chart' => [],
    ]);
});

it('serializes a chart widget with no data as empty labels and values', function () {
    expect((new EmptyDataChartWidget)->toArray(new Request))->toBe([
        'component' => 'chart-widget',
        'heading' => 'Signups',
        'labels' => [],
        'values' => [],
    ]);
});

it('makes widgets visible by default', function () {
    expect(DefaultsOnlyStatWidget::canSee(new Request))->toBeTrue();
});
