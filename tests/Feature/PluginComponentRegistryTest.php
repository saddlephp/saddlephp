<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use SaddlePHP\Fields\Text;
use SaddlePHP\Widgets\Widget;

it('exposes a field component name publicly', function () {
    // A plugin registering a Vue component needs to know which key the
    // frontend will dispatch on, without reading a protected property.
    expect(Text::make('name')->component())->toBe('text-field');
});

it('lets a field declare its own component so a plugin can render it', function () {
    expect(Text::make('name')->component('color-picker-field')->component())
        ->toBe('color-picker-field');
});

it('carries the declared component through to the payload', function () {
    expect(Text::make('name')->component('color-picker-field')->toArray()['component'])
        ->toBe('color-picker-field');
});

it('exposes a widget component name publicly', function () {
    $widget = new class extends Widget
    {
        protected string $component = 'gauge-widget';

        public function toArray(Request $request): array
        {
            return ['component' => $this->component];
        }
    };

    expect($widget->component())->toBe('gauge-widget');
});

it('ships a frontend registry plugins can register components on', function () {
    $bundle = glob(__DIR__.'/../../dist/assets/*.js');

    expect($bundle)->not->toBeEmpty();

    $contents = file_get_contents($bundle[0]);

    // Property names on the exposed global survive minification, so this
    // asserts the seam a plugin script actually calls. Compared as booleans so
    // a failure reports the missing name instead of dumping the whole bundle.
    expect(str_contains($contents, 'registerField'))->toBeTrue()
        ->and(str_contains($contents, 'registerLayout'))->toBeTrue()
        ->and(str_contains($contents, 'registerWidget'))->toBeTrue();
});
