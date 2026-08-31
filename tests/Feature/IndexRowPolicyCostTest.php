<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use SaddlePHP\Tests\Fixtures\CountingHorsePolicy;
use Workbench\App\Models\Horse;

beforeEach(function () {
    CountingHorsePolicy::reset();
    Gate::policy(Horse::class, CountingHorsePolicy::class);
    $this->actingAsUser();
});

it('does not evaluate restore or forceDelete for live rows', function () {
    Horse::factory()->count(5)->create();

    $this->get('/admin/resources/horses')->assertOk();

    // The Vue template only reads restore/forceDelete inside v-else on
    // row.trashed, so for a page of live rows both answers are discarded.
    expect(CountingHorsePolicy::count('restore'))->toBe(0)
        ->and(CountingHorsePolicy::count('forceDelete'))->toBe(0);
});

it('still evaluates view, update and delete for live rows', function () {
    Horse::factory()->count(5)->create();

    $this->get('/admin/resources/horses')->assertOk();

    expect(CountingHorsePolicy::count('view'))->toBe(5)
        ->and(CountingHorsePolicy::count('update'))->toBe(5)
        ->and(CountingHorsePolicy::count('delete'))->toBe(5);
});

it('does not evaluate view, update or delete for trashed rows', function () {
    Horse::factory()->count(3)->create()->each->delete();

    $this->get('/admin/resources/horses?filter[trashed]=only')->assertOk();

    expect(CountingHorsePolicy::count('view'))->toBe(0)
        ->and(CountingHorsePolicy::count('update'))->toBe(0)
        ->and(CountingHorsePolicy::count('delete'))->toBe(0);
});

it('evaluates restore and forceDelete for trashed rows', function () {
    Horse::factory()->count(3)->create()->each->delete();

    $this->get('/admin/resources/horses?filter[trashed]=only')->assertOk();

    expect(CountingHorsePolicy::count('restore'))->toBe(3)
        ->and(CountingHorsePolicy::count('forceDelete'))->toBe(3);
});
