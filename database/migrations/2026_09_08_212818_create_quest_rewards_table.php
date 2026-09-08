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
        Schema::create('quest_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_quest_id')->constrained('user_quests')->cascadeOnDelete();
            $table->string('reward_type');
            $table->json('reward_payload');
            $table->datetime('granted_at');
            $table->timestamps();
            $table->unique(['user_quest_id', 'reward_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quest_rewards');
    }
};
