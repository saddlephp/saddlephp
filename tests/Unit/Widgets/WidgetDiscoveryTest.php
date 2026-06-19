<?php

declare(strict_types=1);

use SaddlePHP\Saddle;
use SaddlePHP\Support\WidgetDiscovery;
use Workbench\App\Saddle\Widgets\HorseCountWidget;
use Workbench\App\Saddle\Widgets\HorsesByBreedWidget;

it('discovers non-abstract widgets ordered by sort', function () {
    $dir = dirname(__DIR__, 2).'/../workbench/app/Saddle/Widgets';
    $found = WidgetDiscovery::in($dir, 'Workbench\\App\\Saddle\\Widgets');

    expect($found)->toContain(HorseCountWidget::class)
        ->and($found)->toContain(HorsesByBreedWidget::class)
        ->and(array_search(HorseCountWidget::class, $found, true))
        ->toBeLessThan(array_search(HorsesByBreedWidget::class, $found, true)); // sort 0 before 1
});

it('returns explicitly registered widgets over discovery', function () {
    app(Saddle::class)->registerWidgets([HorsesByBreedWidget::class]);

    expect(app(Saddle::class)->widgets()->all())->toBe([HorsesByBreedWidget::class]);
});
