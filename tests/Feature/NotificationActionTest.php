<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
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

it('marks all read with a single bulk update regardless of count', function () {
    $user = $this->actingAsUser();
    $user->notify(new HorseEscaped('Cisco'));
    $user->notify(new HorseEscaped('Dakota'));
    $user->notify(new HorseEscaped('Scout'));

    $updates = 0;
    DB::listen(function ($query) use (&$updates) {
        $sql = strtolower(trim($query->sql));
        if (str_starts_with($sql, 'update') && str_contains($sql, 'notifications')) {
            $updates++;
        }
    });

    $this->post('/admin/notifications/read-all')->assertRedirect();

    expect($updates)->toBe(1)
        ->and($user->refresh()->unreadNotifications()->count())->toBe(0);
});
