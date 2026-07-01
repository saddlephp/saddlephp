<?php

declare(strict_types=1);

use SaddlePHP\Saddle;
use Workbench\App\Models\Ranch;
use Workbench\App\Saddle\RanchResource;

it('keeps English flash messages from the lang file', function () {
    $this->actingAsUser();

    $this->post('/admin/resources/horses', ['name' => 'Cisco', 'breed' => 'mustang'])
        ->assertSessionHas('success', 'Horse created.');
});

it('translates flash messages with the active locale', function () {
    app()->setLocale('es');
    app('translator')->addLines(['panel.flash.created' => ':resource creado.'], 'es', 'saddle');
    $this->actingAsUser();

    $this->post('/admin/resources/horses', ['name' => 'Cisco', 'breed' => 'mustang'])
        ->assertSessionHas('success', 'Horse creado.');
});

it('keeps English relation-record flash messages by default', function () {
    app(Saddle::class)->register([RanchResource::class]);
    $this->actingAsUser();
    $ranch = Ranch::factory()->create();

    $this->post("/admin/resources/ranches/{$ranch->id}/relations/horses", ['name' => 'Cisco', 'is_saddled' => true])
        ->assertSessionHas('success', 'Horse created.');
});

it('translates relation-record flash messages with the active locale', function () {
    app()->setLocale('es');
    app('translator')->addLines(['panel.flash.created' => ':resource creado.'], 'es', 'saddle');
    app(Saddle::class)->register([RanchResource::class]);
    $this->actingAsUser();
    $ranch = Ranch::factory()->create();

    $this->post("/admin/resources/ranches/{$ranch->id}/relations/horses", ['name' => 'Cisco', 'is_saddled' => true])
        ->assertSessionHas('success', 'Horse creado.');
});
