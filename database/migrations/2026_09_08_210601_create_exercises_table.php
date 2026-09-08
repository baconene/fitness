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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_category_id')->constrained('exercise_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('exercise_type');
            $table->json('equipment_required')->nullable();
            $table->string('difficulty')->default('beginner');
            $table->json('primary_muscle')->nullable();
            $table->json('secondary_muscles')->nullable();
            $table->text('instructions')->nullable();
            $table->string('video_url')->nullable();
            $table->string('image_url')->nullable();
            $table->json('contraindications')->nullable();
            $table->unsignedSmallInteger('xp_base_value')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['exercise_category_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
