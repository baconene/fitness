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
        Schema::create('program_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_day_id')->constrained('program_days')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->unsignedSmallInteger('order');
            $table->unsignedTinyInteger('target_sets')->default(3);
            $table->unsignedTinyInteger('target_reps_min')->default(8);
            $table->unsignedTinyInteger('target_reps_max')->default(12);
            $table->decimal('target_weight_pct', 3, 0)->nullable();
            $table->unsignedSmallInteger('rest_seconds')->default(60);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_exercises');
    }
};
