<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guarded: hosts that already ran Laravel's notifications:table keep theirs.
        if (Schema::hasTable('notifications')) {
            return;
        }

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Symmetric with the guarded up(): notifications is a table Laravel's own
        // notifications:table may own. Never drop it while it holds data, so a
        // rollback can't destroy a host's (or Saddle's) notifications. An empty
        // table is safe to remove.
        if (Schema::hasTable('notifications') && DB::table('notifications')->count() === 0) {
            Schema::drop('notifications');
        }
    }
};
