<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Notifications\HorseEscaped;

it('marks a single notification read for its owner', function () {
    $user = $this->actingAsUser();
    $user->notify(new HorseEscaped('Cisco'));
    $id = $user->notifications()->first()->id;

    $this->post("/admin/notifications/{$id}/read")->assertRedirect();

    expect($user->refresh()->unreadNotifications()->count())->toBe(0);
});

it('404s marking another user\'s notification read', function () {
    $owner = User::factory()->create();
    $owner->notify(new HorseEscaped('Cisco'));
    $id = $owner->notifications()->first()->id;

    $this->actingAsUser(); // a different user
    $this->post("/admin/notifications/{$id}/read")->assertNotFound();

    expect($owner->refresh()->unreadNotifications()->count())->toBe(1);
});

it('marks all notifications read', function () {
    $user = $this->actingAsUser();
    $user->notify(new HorseEscaped('Cisco'));
    $user->notify(new HorseEscaped('Dakota'));

    $this->post('/admin/notifications/read-all')->assertRedirect();

    expect($user->refresh()->unreadNotifications()->count())->toBe(0);
});
