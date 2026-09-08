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
        Schema::create('quest_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('quest_type', ['Daily', 'Weekly', 'OneTime']);
            $table->string('category');
            $table->string('difficulty');
            $table->string('target_metric');
            $table->integer('target_value');
            $table->integer('xp_reward_base');
            $table->json('stat_reward')->nullable();
            $table->string('min_rank')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quest_templates');
    }
};
