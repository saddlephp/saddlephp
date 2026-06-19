<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Workbench\App\Models\User;
use Workbench\App\Notifications\HorseEscaped;

it('provides a notifications table', function () {
    expect(Schema::hasTable('notifications'))->toBeTrue();
});

it('makes the user notifiable', function () {
    $user = User::factory()->create();
    $user->notify(new HorseEscaped('Cisco'));

    expect($user->notifications()->count())->toBe(1)
        ->and($user->unreadNotifications()->count())->toBe(1);
});
