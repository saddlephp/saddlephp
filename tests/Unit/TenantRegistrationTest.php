<?php

declare(strict_types=1);

use SaddlePHP\Saddle;
use Workbench\App\Saddle\RanchRegistration;

it('exposes the configured registration handler', function () {
    expect((new Saddle)->canRegisterTenant())->toBeFalse();

    config()->set('saddle.tenancy.registration', RanchRegistration::class);

    expect((new Saddle)->canRegisterTenant())->toBeTrue()
        ->and((new Saddle)->tenantRegistration())->toBeInstanceOf(RanchRegistration::class);
});
