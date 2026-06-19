<?php

declare(strict_types=1);

use Workbench\App\Models\Horse;

it('injects validated theme tokens into the panel head', function () {
    config()->set('saddle.brand.theme', ['ink' => '#222222']);
    $this->actingAsUser();
    Horse::factory()->create();

    $this->get('/admin/resources/horses')
        ->assertOk()
        ->assertSee('--color-ink: #222222;', false);
});
