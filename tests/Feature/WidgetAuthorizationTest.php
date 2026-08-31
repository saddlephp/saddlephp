<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use SaddlePHP\Saddle;
use SaddlePHP\Tests\Fixtures\DenyViewAnyPolicy;
use SaddlePHP\Widgets\StatWidget;
use Workbench\App\Models\Horse;
use Workbench\App\Saddle\Widgets\HorseCountWidget;

it('hides a resource-backed widget when the resource denies viewAny', function () {
    Gate::policy(Horse::class, DenyViewAnyPolicy::class);
    app(Saddle::class)->registerWidgets([HorseCountWidget::class]);
    $this->actingAsUser();
    Horse::factory()->create();

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('widgets', fn ($widgets) => collect($widgets)
                ->doesntContain(fn ($w) => ($w['label'] ?? null) === 'Horses'))
        );
});

it('shows a resource-backed widget when the resource allows viewAny', function () {
    app(Saddle::class)->registerWidgets([HorseCountWidget::class]);
    $this->actingAsUser();
    Horse::factory()->create();

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('widgets', fn ($widgets) => collect($widgets)
                ->contains(fn ($w) => ($w['label'] ?? null) === 'Horses'))
        );
});

it('hides a widget that declares no resource, because nothing can authorize it', function () {
    app(Saddle::class)->registerWidgets([UnownedWidget::class]);
    $this->actingAsUser();

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('widgets', fn ($widgets) => collect($widgets)
                ->doesntContain(fn ($w) => ($w['label'] ?? null) === 'Unowned'))
        );
});

it('lets a widget with no resource opt back in by overriding canSee', function () {
    app(Saddle::class)->registerWidgets([DeliberatelyPublicWidget::class]);
    $this->actingAsUser();

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('widgets', fn ($widgets) => collect($widgets)
                ->contains(fn ($w) => ($w['label'] ?? null) === 'Public'))
        );
});

it('shows an unowned widget when the fail-closed default is turned off', function () {
    config()->set('saddle.authorization.require_widget_resource', false);
    app(Saddle::class)->registerWidgets([UnownedWidget::class]);
    $this->actingAsUser();

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('widgets', fn ($widgets) => collect($widgets)
                ->contains(fn ($w) => ($w['label'] ?? null) === 'Unowned'))
        );
});

class UnownedWidget extends StatWidget
{
    public function label(): string
    {
        return 'Unowned';
    }

    public function value(Request $request): string|int
    {
        return 1;
    }
}

class DeliberatelyPublicWidget extends StatWidget
{
    public static function canSee(Request $request): bool
    {
        return true;
    }

    public function label(): string
    {
        return 'Public';
    }

    public function value(Request $request): string|int
    {
        return 2;
    }
}
