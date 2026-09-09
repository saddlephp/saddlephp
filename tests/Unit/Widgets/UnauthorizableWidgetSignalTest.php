<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use SaddlePHP\Tests\Fixtures\DenyViewAnyPolicy;
use SaddlePHP\Widgets\StatWidget;
use Workbench\App\Models\Horse;
use Workbench\App\Saddle\HorseResource;

/**
 * The fail-closed default is right -- an aggregate discloses more than it looks
 * like it does. But a widget that declares no resource and does not override
 * canSee() simply did not appear, with nothing anywhere saying why: the
 * dashboard was just missing a tile. Only someone who read the 1.4.0 diff would
 * know the rule exists.
 */
class SilentlyHiddenWidget extends StatWidget
{
    public function label(): string
    {
        return 'Hidden';
    }

    public function value(Request $request): string|int
    {
        return 1;
    }
}

class OwnedWidget extends StatWidget
{
    public static ?string $resource = HorseResource::class;

    public function label(): string
    {
        return 'Owned';
    }

    public function value(Request $request): string|int
    {
        return 1;
    }
}

class OptedInWidget extends StatWidget
{
    public static function canSee(Request $request): bool
    {
        return true;
    }

    public function label(): string
    {
        return 'Opted in';
    }

    public function value(Request $request): string|int
    {
        return 1;
    }
}

it('logs why an unauthorizable widget was hidden, and still hides it', function () {
    Log::spy();

    expect(SilentlyHiddenWidget::canSee(new Request))->toBeFalse();

    Log::shouldHaveReceived('warning')->withArgs(fn (string $message) => str_contains($message, SilentlyHiddenWidget::class)
        && str_contains($message, '$resource')
        && str_contains($message, 'canSee()')
        && str_contains($message, 'require_widget_resource'))->once();
});

/**
 * Locally it throws instead, matching how the package already treats an
 * unusable declaration (a relation column asked to sort, a CustomColumn with no
 * tag). A live panel losing a tile must never become a live panel losing its
 * dashboard, so the throw is confined to `local`.
 */
it('throws in local, where a missing tile would otherwise go unnoticed', function () {
    app()->detectEnvironment(fn () => 'local');

    SilentlyHiddenWidget::canSee(new Request);
})->throws(LogicException::class, SilentlyHiddenWidget::class);

it('does not throw outside local', function () {
    Log::spy();
    app()->detectEnvironment(fn () => 'production');

    expect(SilentlyHiddenWidget::canSee(new Request))->toBeFalse();
});

/**
 * The way to silence it is to fix the widget. Each of these is a widget that
 * can be authorized, so there is nothing to report -- including the opt-out,
 * where the widget renders.
 */
it('says nothing when the widget can actually be authorized', function () {
    Log::spy();
    app()->detectEnvironment(fn () => 'local');

    expect(OwnedWidget::canSee(new Request))->toBeTrue()
        ->and(OptedInWidget::canSee(new Request))->toBeTrue();

    config()->set('saddle.authorization.require_widget_resource', false);
    expect(SilentlyHiddenWidget::canSee(new Request))->toBeTrue();

    Log::shouldNotHaveReceived('warning');
});

it('says nothing when a declared resource simply denies the user', function () {
    Log::spy();
    app()->detectEnvironment(fn () => 'local');
    Gate::policy(Horse::class, DenyViewAnyPolicy::class);

    // Denied is not misconfigured: the policy was consulted and said no.
    expect(OwnedWidget::canSee(new Request))->toBeFalse();

    Log::shouldNotHaveReceived('warning');
});
