<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('boss_damage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boss_encounter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_set_id')->nullable()->constrained()->nullableOnDelete();
            $table->string('source_type');
            $table->integer('damage_amount');
            $table->string('idempotency_key')->unique();
            $table->timestamps();
            $table->index('boss_encounter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boss_damage_events');
    }
};
