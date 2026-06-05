<?php

declare(strict_types=1);

it('injects registered plugin assets into the panel shell', function () {
    $this->actingAsUser();

    $response = $this->get('/admin');

    $response->assertOk()
        ->assertSee('vendor/saddle-demo/rating-field.js', false)
        ->assertSee('vendor/saddle-demo/rating-field.css', false);
});
