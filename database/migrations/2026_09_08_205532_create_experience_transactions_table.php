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
        Schema::create('experience_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hunter_profile_id')->constrained('hunter_profiles')->cascadeOnDelete();
            $table->integer('amount');
            $table->nullableMorphs('source');
            $table->unsignedBigInteger('balance_after');
            $table->string('idempotency_key')->unique();
            $table->timestamp('awarded_at');
            $table->timestamps();

            $table->index(['hunter_profile_id', 'awarded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experience_transactions');
    }
};
