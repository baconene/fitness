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
        Schema::create('training_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('experience_level')->default('beginner');
            $table->unsignedTinyInteger('days_per_week')->default(3);
            $table->json('preferred_days')->nullable();
            $table->unsignedSmallInteger('session_duration_minutes')->default(60);
            $table->string('training_focus')->default('general_fitness');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_preferences');
    }
};
