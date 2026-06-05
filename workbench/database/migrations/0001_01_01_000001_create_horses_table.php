<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('breed')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_saddled')->default(false);
            $table->foreignId('rider_id')->nullable();
            $table->foreignId('ranch_id')->nullable();
            $table->integer('age')->nullable();
            $table->date('foaled_on')->nullable();
            $table->string('photo')->nullable();
            $table->dateTime('last_vet_visit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horses');
    }
};
