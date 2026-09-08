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
        Schema::create('hunter_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('codename')->unique()->nullable();
            $table->string('rank')->default('E');
            $table->unsignedInteger('current_level')->default(1);
            $table->unsignedInteger('current_xp')->default(0);
            $table->unsignedBigInteger('total_xp_earned')->default(0);
            $table->timestamp('awakened_at')->nullable();
            $table->json('avatar_meta')->nullable();
            $table->timestamps();

            $table->index('rank');
            $table->index('current_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hunter_profiles');
    }
};
