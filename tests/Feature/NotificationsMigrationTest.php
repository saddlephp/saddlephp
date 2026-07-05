<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('does not drop the notifications table on rollback while it holds data', function () {
    $migration = require dirname(__DIR__, 2).'/database/migrations/2026_01_01_000000_create_saddle_notifications_table.php';

    DB::table('notifications')->insert([
        'id' => '11111111-1111-1111-1111-111111111111',
        'type' => 'App\\Notifications\\Demo',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => 1,
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // A rollback must never destroy existing notifications (which may be a
    // host's, since up() no-ops when the table already exists).
    $migration->down();

    expect(Schema::hasTable('notifications'))->toBeTrue()
        ->and(DB::table('notifications')->count())->toBe(1);
});
