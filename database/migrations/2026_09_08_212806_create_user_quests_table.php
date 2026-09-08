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
        Schema::create('user_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quest_template_id')->constrained('quest_templates')->cascadeOnDelete();
            $table->enum('status', ['Active', 'Completed', 'Expired']);
            $table->date('assigned_date');
            $table->date('expires_at');
            $table->datetime('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'quest_template_id', 'assigned_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quests');
    }
};
