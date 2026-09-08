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
        Schema::create('personal_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->string('record_type');
            $table->decimal('value', 10, 2);
            $table->string('unit');
            $table->foreignId('workout_set_id')->nullable()->constrained('workout_sets')->nullOnDelete();
            $table->boolean('is_current')->default(true);
            $table->timestamp('achieved_at');
            $table->timestamps();

            $table->index(['user_id', 'exercise_id', 'record_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_records');
    }
};
