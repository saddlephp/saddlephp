<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        // Deliberately does nothing.
        //
        // up() is guarded, so this migration may never have created the table:
        // Laravel's own notifications:table produces a byte-identical schema,
        // which means ownership cannot be inferred from the table itself. The
        // previous guard here dropped the table whenever it was *empty*, so a
        // host that created it themselves and simply had no notifications yet
        // -- every fresh dev and staging environment -- lost it to a routine
        // migrate:rollback, and their own migration was still recorded as run,
        // so it never came back.
        //
        // Leaving an unused table behind is a tidiness problem. Dropping one
        // that belongs to somebody else is a data-loss problem. To remove it
        // deliberately: Schema::dropIfExists('notifications').
    }
};
