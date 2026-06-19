<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use SaddlePHP\Widgets\ChartWidget;
use SaddlePHP\Widgets\StatWidget;

class FixtureStat extends StatWidget
{
    public function label(): string
    {
        return 'Horses';
    }

    public function value(Request $request): string|int
    {
        return 7;
    }

    public function description(Request $request): ?string
    {
        return '3 saddled';
    }

    public function chart(Request $request): array
    {
        return [1, 2, 3];
    }
}

class FixtureChart extends ChartWidget
{
    public function heading(): string
    {
        return 'By breed';
    }

    public function data(Request $request): array
    {
        return ['quarter' => 5, 'mustang' => 2];
    }
}

it('serializes a stat widget with an inline chart', function () {
    expect((new FixtureStat)->toArray(new Request))->toBe([
        'component' => 'stat-widget',
        'label' => 'Horses',
        'value' => '7',
        'description' => '3 saddled',
        'chart' => [1, 2, 3],
    ]);
});

it('serializes a chart widget with aligned labels and values', function () {
    expect((new FixtureChart)->toArray(new Request))->toBe([
        'component' => 'chart-widget',
        'heading' => 'By breed',
        'labels' => ['quarter', 'mustang'],
        'values' => [5, 2],
    ]);
});
