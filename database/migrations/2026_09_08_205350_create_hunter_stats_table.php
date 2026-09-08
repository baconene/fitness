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
        Schema::create('hunter_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hunter_profile_id')->unique()->constrained('hunter_profiles')->cascadeOnDelete();
            $table->unsignedSmallInteger('strength')->default(10);
            $table->unsignedSmallInteger('endurance')->default(10);
            $table->unsignedSmallInteger('agility')->default(10);
            $table->unsignedSmallInteger('vitality')->default(10);
            $table->unsignedSmallInteger('willpower')->default(10);
            $table->unsignedInteger('stat_points_available')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hunter_stats');
    }
};
