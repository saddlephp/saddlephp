<?php

declare(strict_types=1);

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
