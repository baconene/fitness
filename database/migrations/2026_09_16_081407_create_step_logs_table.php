<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('step_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // One row per day: a step count is a running total for that day, not
            // an event, so a second entry replaces the first rather than adding.
            $table->date('counted_on');
            $table->unsignedInteger('steps');
            $table->timestamps();

            $table->unique(['user_id', 'counted_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('step_logs');
    }
};
