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
        Schema::create('dungeon_floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dungeon_id')->constrained()->cascadeOnDelete();
            $table->integer('floor_number');
            $table->string('boss_name');
            $table->integer('boss_health');
            $table->integer('xp_reward');
            $table->json('loot_table')->nullable();
            $table->timestamps();
            $table->unique(['dungeon_id', 'floor_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dungeon_floors');
    }
};
