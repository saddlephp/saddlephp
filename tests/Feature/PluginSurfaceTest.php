<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use SaddlePHP\Saddle;
use SaddlePHP\Tests\Fixtures\PluginHorseResource;
use Workbench\App\Models\Horse;

beforeEach(function () {
    app(Saddle::class)->register([PluginHorseResource::class]);
});

it('serves custom field tags on the create form', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/plugin-horses/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('fields.1.component', 'custom-field')
            ->where('fields.1.tag', 'breed-picker')
        );
});

it('stores values submitted through custom fields', function () {
    $this->actingAsUser();

    $this->post('/admin/resources/plugin-horses', ['name' => 'Cisco', 'breed' => 'mustang'])
        ->assertRedirect('/admin/resources/plugin-horses');

    expect(Horse::query()->where('name', 'Cisco')->first()->breed)->toBe('mustang');
});

it('serves custom column tags on the index', function () {
    $this->actingAsUser();
    Horse::factory()->create(['name' => 'Cisco', 'breed' => 'mustang']);

    $this->get('/admin/resources/plugin-horses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('columns.1.type', 'custom')
            ->where('columns.1.tag', 'breed-cell')
            ->where('rows.data.0.cells.breed', 'mustang')
        );
});
