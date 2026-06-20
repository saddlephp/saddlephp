<?php

declare(strict_types=1);

use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Saddle;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Ranch;
use Workbench\App\Saddle\HorseResource;
use Workbench\App\Saddle\RanchResource;
use Workbench\App\Saddle\RiderResource;

it('isolates a runtime $tenant assignment to a single resource', function () {
    // The tenancy suite toggles $tenant at runtime. Each resource must own its
    // storage so the write never leaks to a sibling (which previously inherited
    // the abstract base's shared slot).
    HorseResource::$tenant = 'ranch';

    try {
        expect(RanchResource::$tenant)->toBeNull()
            ->and(RiderResource::$tenant)->toBeNull();
    } finally {
        HorseResource::$tenant = null;
    }
});

it('throws a clear error when $tenant names a relation the model does not have', function () {
    app(Saddle::class)->useTenant(new Ranch);

    try {
        expect(fn () => BogusTenantResource::query(request()))
            ->toThrow(LogicException::class);
    } finally {
        app(Saddle::class)->forgetTenant();
    }
});

class BogusTenantResource extends Resource
{
    public static string $model = Horse::class;

    public static ?string $tenant = 'nonexistent';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }
}
