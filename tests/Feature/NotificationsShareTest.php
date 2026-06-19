<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\User;
use Workbench\App\Notifications\HorseEscaped;

it('shares unread count and recent notification items', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->notify(new HorseEscaped('Cisco'));
    $this->actingAs($user);

    $this->get('/admin')
        ->assertInertia(fn (Assert $page) => $page
            ->where('saddle.notifications.unread', 1)
            ->where('saddle.notifications.items.0.message', 'Cisco has escaped the corral!')
            ->where('saddle.notifications.items.0.read', false)
            ->where('saddle.notifications.items.0.url', '/admin/resources/horses')
        );
});
