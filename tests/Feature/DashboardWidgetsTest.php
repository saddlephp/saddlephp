<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use SaddlePHP\Saddle;
use Workbench\App\Models\Horse;
use Workbench\App\Saddle\Widgets\HorseCountWidget;
use Workbench\App\Saddle\Widgets\HorsesByBreedWidget;

beforeEach(function () {
    app(Saddle::class)->registerWidgets([HorseCountWidget::class, HorsesByBreedWidget::class]);
});

it('renders widgets on the dashboard', function () {
    $this->actingAsUser();
    Horse::factory()->create(['breed' => 'quarter']);

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('widgets.0.component', 'stat-widget')
            ->where('widgets.0.label', 'Horses')
            ->where('widgets.0.value', '1')
            ->where('widgets.1.component', 'chart-widget')
            ->where('widgets.1.heading', 'Horses by breed')
        );
});

it('omits a widget the user cannot see and survives a throwing widget', function () {
    app(Saddle::class)->registerWidgets([HiddenWidget::class, BoomWidget::class]);
    $this->actingAsUser();

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('widgets', fn ($widgets) => collect($widgets)->doesntContain(fn ($w) => ($w['label'] ?? null) === 'Hidden'))
        );
});

class HiddenWidget extends \SaddlePHP\Widgets\StatWidget
{
    public static function canSee(Request $request): bool
    {
        return false;
    }

    public function label(): string
    {
        return 'Hidden';
    }

    public function value(Request $request): string|int
    {
        return 0;
    }
}

class BoomWidget extends \SaddlePHP\Widgets\StatWidget
{
    public function label(): string
    {
        return 'Boom';
    }

    public function value(Request $request): string|int
    {
        throw new \RuntimeException('boom');
    }
}
